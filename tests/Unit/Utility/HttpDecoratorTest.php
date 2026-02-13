<?php
/**
 * @package   joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license   AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Dionysopoulos\Mcp4Joomla\Tests\Unit\Utility;

use Dionysopoulos\Mcp4Joomla\Tests\Unit\Stubs\SpyLogger;
use Dionysopoulos\Mcp4Joomla\Utility\HttpDecorator;
use Joomla\Http\Http;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Pimple\Container as PimpleContainer;
use Pimple\Psr11\Container as Psr11Container;

class HttpDecoratorTest extends TestCase
{
	private Http|MockObject $mockHttp;

	private HttpDecorator $decorator;

	private SpyLogger $logger;

	protected function setUp(): void
	{
		$this->mockHttp = $this->createMock(Http::class);
		$this->logger   = new SpyLogger();

		$pimple        = new PimpleContainer();
		$pimple['env'] = [
			'JOOMLA_BASE_URL' => 'https://example.com',
			'BEARER_TOKEN'    => 'dGVzdHRva2VuMTIz',
		];
		$pimple['log'] = $this->logger;

		$this->decorator = new HttpDecorator(
			$this->mockHttp,
			logRequests: false,
			logResponses: false,
			container: new Psr11Container($pimple),
		);
	}

	public function testGetUriBuildCorrectUrl(): void
	{
		$uri = $this->decorator->getUri('v1/content/articles');
		$this->assertSame('https://example.com/api/index.php/v1/content/articles', $uri->toString());
	}

	public function testGetUriStripsSlashes(): void
	{
		$uri = $this->decorator->getUri('/v1/tags/');
		$this->assertSame('https://example.com/api/index.php/v1/tags', $uri->toString());
	}

	public function testGetUriWithTrailingSlashOnBaseUrl(): void
	{
		$pimple        = new PimpleContainer();
		$pimple['env'] = [
			'JOOMLA_BASE_URL' => 'https://example.com/',
			'BEARER_TOKEN'    => 'dGVzdHRva2VuMTIz',
		];
		$pimple['log'] = $this->logger;

		$decorator = new HttpDecorator(
			$this->mockHttp,
			logRequests: false,
			logResponses: false,
			container: new Psr11Container($pimple),
		);

		$uri = $decorator->getUri('v1/content/articles');
		$this->assertSame('https://example.com/api/index.php/v1/content/articles', $uri->toString());
	}

	public function testDefaultHeadersContainAuth(): void
	{
		$headers = $this->decorator->getDefaultHeaders();

		$this->assertArrayHasKey('Authorization', $headers);
		$this->assertArrayHasKey('X-Joomla-Token', $headers);
		$this->assertArrayHasKey('Accept', $headers);
		$this->assertSame('Bearer dGVzdHRva2VuMTIz', $headers['Authorization']);
		$this->assertSame('dGVzdHRva2VuMTIz', $headers['X-Joomla-Token']);
		$this->assertSame('application/vnd.api+json', $headers['Accept']);
	}

	public function testGetDelegatesToHttp(): void
	{
		$expectedResponse = createJoomlaResponse(200, '{"data":[]}');

		$this->mockHttp
			->expects($this->once())
			->method('get')
			->willReturn($expectedResponse);

		$response = $this->decorator->get('https://example.com/api/index.php/v1/content/articles');
		$this->assertSame(200, $response->getStatusCode());
	}

	public function testPostDelegatesToHttp(): void
	{
		$expectedResponse = createJoomlaResponse(201, '{"data":{}}');

		$this->mockHttp
			->expects($this->once())
			->method('post')
			->willReturn($expectedResponse);

		$response = $this->decorator->post('https://example.com/api/test', '{"title":"Test"}');
		$this->assertSame(201, $response->getStatusCode());
	}

	public function testPatchDelegatesToHttp(): void
	{
		$expectedResponse = createJoomlaResponse(200, '{"data":{}}');

		$this->mockHttp
			->expects($this->once())
			->method('patch')
			->willReturn($expectedResponse);

		$response = $this->decorator->patch('https://example.com/api/test', '{"title":"Updated"}');
		$this->assertSame(200, $response->getStatusCode());
	}

	public function testDeleteDelegatesToHttp(): void
	{
		$expectedResponse = createJoomlaResponse(204);

		$this->mockHttp
			->expects($this->once())
			->method('delete')
			->willReturn($expectedResponse);

		$response = $this->decorator->delete('https://example.com/api/test');
		$this->assertSame(204, $response->getStatusCode());
	}

	public function testGetOptionDelegates(): void
	{
		$this->mockHttp
			->expects($this->once())
			->method('getOption')
			->with('timeout', null)
			->willReturn(10);

		$this->assertSame(10, $this->decorator->getOption('timeout'));
	}

	public function testSetOptionDelegates(): void
	{
		$this->mockHttp
			->expects($this->once())
			->method('setOption')
			->with('timeout', 30);

		$this->decorator->setOption('timeout', 30);
	}
}

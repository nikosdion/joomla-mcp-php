<?php
/**
 * @package   joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license   AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Dionysopoulos\Mcp4Joomla\Tests\Unit\Stubs;

use Dionysopoulos\Mcp4Joomla\Container\Factory;
use Dionysopoulos\Mcp4Joomla\Utility\HttpDecorator;
use Joomla\Http\Http;
use PHPUnit\Framework\MockObject\MockObject;
use Pimple\Container as PimpleContainer;
use Pimple\Psr11\Container as Psr11Container;

/**
 * Trait to set up a test DI container with a mock Http client wrapped in a real HttpDecorator.
 *
 * Provides $this->mockHttp (MockObject|Http) and $this->spyLogger (SpyLogger).
 */
trait TestContainerTrait
{
	protected Http|MockObject $mockHttp;

	protected SpyLogger $spyLogger;

	protected HttpDecorator $httpDecorator;

	/**
	 * Sets up the Factory singleton with a test container.
	 *
	 * Call this from setUp() in your test class.
	 */
	protected function setUpTestContainer(): void
	{
		$this->mockHttp  = $this->createMock(Http::class);
		$this->spyLogger = new SpyLogger();

		$pimple = new PimpleContainer();

		$pimple['env'] = [
			'JOOMLA_BASE_URL' => 'https://example.com',
			'BEARER_TOKEN'    => 'dGVzdHRva2VuMTIz',
		];

		$pimple['log'] = $this->spyLogger;

		$container = new Psr11Container($pimple);

		// Create a real HttpDecorator wrapping the mock Http, with logging disabled
		$this->httpDecorator = new HttpDecorator(
			$this->mockHttp,
			logRequests: false,
			logResponses: false,
			container: $container,
		);

		$pimple['http'] = $this->httpDecorator;

		// Replace the Factory singleton
		Factory::setContainer(new Psr11Container($pimple));
	}

	/**
	 * Resets the Factory singleton after each test.
	 *
	 * Call this from tearDown() in your test class.
	 */
	protected function tearDownTestContainer(): void
	{
		Factory::reset();
	}
}

<?php
/**
 * @package   joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license   AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Dionysopoulos\Mcp4Joomla\Tests\Unit\Container;

use Dionysopoulos\Mcp4Joomla\Container\EnvironmentProvider;
use PHPUnit\Framework\TestCase;
use Pimple\Container;

class EnvironmentProviderTest extends TestCase
{
	private array $originalServer = [];

	protected function setUp(): void
	{
		$this->originalServer = $_SERVER;
	}

	protected function tearDown(): void
	{
		$_SERVER = $this->originalServer;
	}

	public function testValidEnvironment(): void
	{
		$_SERVER['JOOMLA_BASE_URL'] = 'https://example.com';
		$_SERVER['BEARER_TOKEN']    = 'dGVzdHRva2VuMTIz';

		$pimple = new Container();
		$provider = new EnvironmentProvider();
		$provider->register($pimple);

		$env = $pimple['env'];

		$this->assertSame('https://example.com', $env['JOOMLA_BASE_URL']);
		$this->assertSame('dGVzdHRva2VuMTIz', $env['BEARER_TOKEN']);
	}

	public function testMissingUrlThrows(): void
	{
		unset($_SERVER['JOOMLA_BASE_URL']);
		$_SERVER['BEARER_TOKEN'] = 'dGVzdHRva2VuMTIz';

		$pimple = new Container();
		$provider = new EnvironmentProvider();
		$provider->register($pimple);

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('JOOMLA_BASE_URL');

		$pimple['env'];
	}

	public function testInvalidUrlThrows(): void
	{
		$_SERVER['JOOMLA_BASE_URL'] = 'not-a-url';
		$_SERVER['BEARER_TOKEN']    = 'dGVzdHRva2VuMTIz';

		$pimple = new Container();
		$provider = new EnvironmentProvider();
		$provider->register($pimple);

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('JOOMLA_BASE_URL');

		$pimple['env'];
	}

	public function testMissingTokenThrows(): void
	{
		$_SERVER['JOOMLA_BASE_URL'] = 'https://example.com';
		unset($_SERVER['BEARER_TOKEN']);

		$pimple = new Container();
		$provider = new EnvironmentProvider();
		$provider->register($pimple);

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('BEARER_TOKEN');

		$pimple['env'];
	}

	public function testInvalidTokenThrows(): void
	{
		$_SERVER['JOOMLA_BASE_URL'] = 'https://example.com';
		$_SERVER['BEARER_TOKEN']    = '!!!invalid-base64!!!';

		$pimple = new Container();
		$provider = new EnvironmentProvider();
		$provider->register($pimple);

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('BEARER_TOKEN');

		$pimple['env'];
	}
}

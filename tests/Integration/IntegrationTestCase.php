<?php
/**
 * @package   joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license   AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Dionysopoulos\Mcp4Joomla\Tests\Integration;

use Composer\CaBundle\CaBundle;
use Dionysopoulos\Mcp4Joomla\Container\Factory;
use Dionysopoulos\Mcp4Joomla\Utility\HttpDecorator;
use Joomla\Http\HttpFactory;
use PHPUnit\Framework\TestCase;
use Pimple\Container as PimpleContainer;
use Pimple\Psr11\Container as Psr11Container;
use Psr\Log\NullLogger;

/**
 * Abstract base class for integration tests.
 *
 * Loads credentials from tests/integration.config.php,
 * skips tests if the config file is missing.
 */
abstract class IntegrationTestCase extends TestCase
{
	protected static array $config = [];

	protected HttpDecorator $http;

	public static function setUpBeforeClass(): void
	{
		$configFile = dirname(__DIR__) . '/integration.config.php';

		if (!file_exists($configFile))
		{
			static::markTestSkipped(
				'Integration config not found. Copy tests/integration.config.php.dist to tests/integration.config.php.'
			);
		}

		static::$config = require $configFile;

		if (
			empty(static::$config['JOOMLA_BASE_URL'])
			|| empty(static::$config['BEARER_TOKEN'])
		)
		{
			static::markTestSkipped('Integration config is incomplete.');
		}
	}

	protected function setUp(): void
	{
		$pimple = new PimpleContainer();

		$pimple['env'] = [
			'JOOMLA_BASE_URL' => static::$config['JOOMLA_BASE_URL'],
			'BEARER_TOKEN'    => static::$config['BEARER_TOKEN'],
		];

		$pimple['log'] = new NullLogger();

		$container = new Psr11Container($pimple);

		$realHttp = (new HttpFactory())->getHttp([
			'curl.certpath'   => CaBundle::getBundledCaBundlePath(),
			'stream.certpath' => CaBundle::getBundledCaBundlePath(),
			'follow_location' => true,
			'timeout'         => 15,
			'userAgent'       => 'Mcp4Joomla/test',
		]);

		$this->http = new HttpDecorator(
			$realHttp,
			logRequests: false,
			logResponses: false,
			container: $container,
		);

		$pimple['http'] = $this->http;

		Factory::setContainer(new Psr11Container($pimple));
	}

	protected function tearDown(): void
	{
		Factory::reset();
	}
}

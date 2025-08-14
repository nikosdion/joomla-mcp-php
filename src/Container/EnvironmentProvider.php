<?php
/**
 * @package       joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license       AGPL-3.0-or-later
 */

namespace Dionysopoulos\Mcp4Joomla\Container;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class EnvironmentProvider implements ServiceProviderInterface
{
	private const BASE64_PATTERN = '/^[A-Za-z0-9+\/]+={0,2}$/';

	/**
	 * @inheritDoc
	 */
	public function register(Container $pimple)
	{
		$pimple['env'] = function (Container $c) {
			$env = [];

			$env['JOOMLA_BASE_URL'] = filter_var($_SERVER['JOOMLA_BASE_URL'] ?? '', FILTER_VALIDATE_URL, FILTER_NULL_ON_FAILURE) ?: null;

			if (empty($env['JOOMLA_BASE_URL']))
			{
				throw new \RuntimeException('JOOMLA_BASE_URL must be a valid URL');
			}

			if (!isset($_SERVER['BEARER_TOKEN']) || !preg_match(self::BASE64_PATTERN, $_SERVER['BEARER_TOKEN']))
			{
				throw new \RuntimeException('BEARER_TOKEN must be a valid base64-encoded string');
			}
			
			$env['BEARER_TOKEN'] = $_SERVER['BEARER_TOKEN'];
			
			return $env;
		};
	}
}
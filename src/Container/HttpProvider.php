<?php
/**
 * @package       joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license       AGPL-3.0-or-later
 */

namespace Dionysopoulos\Mcp4Joomla\Container;

use Composer\CaBundle\CaBundle;
use Joomla\Http\HttpFactory;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class HttpProvider implements ServiceProviderInterface
{
	public function register(Container $pimple)
	{
		$pimple['http'] = fn(Container $c) => (new HttpFactory())
			->getHttp(
				[
					'curl.certpath'   => CaBundle::getBundledCaBundlePath(),
					'stream.certpath' => CaBundle::getBundledCaBundlePath(),
					'follow_location' => true,
					'timeout'         => 10,
					'userAgent'       => sprintf('Mcp4Joomla/%s', MCP4JOOMLA_VERSION),
					'headers'         => [
						// This is necessary for Joomla to process the request.
						'Accept'   => 'application/vnd.api+json',
						// This works on most servers
						'Authorization'  => sprintf("Bearer %s", $_ENV['BEARER_TOKEN'] ?? 'invalid'),
						// This works on servers which don't have the PHP fix for auth headers in their config.
						'X-Joomla-Token' => $_ENV['JOOMLA_TOKEN'] ?? 'invalid',
					],
				]
			);
	}
}
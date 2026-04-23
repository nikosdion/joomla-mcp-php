<?php
/**
 * @package       joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license       AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Dionysopoulos\Mcp4Joomla\Container;

use Composer\CaBundle\CaBundle;
use Dionysopoulos\Mcp4Joomla\Utility\HttpDecorator;
use Joomla\Http\HttpFactory;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class HttpProvider implements ServiceProviderInterface
{
	public function register(Container $pimple)
	{
		$pimple['http'] = // This is necessary for Joomla to process the request.
			// This works on most servers
			// This works on servers which don't have the PHP fix for auth headers in their config.
		fn(Container $c) => new HttpDecorator((new HttpFactory())
			->getHttp(
				[
					'curl.certpath'   => CaBundle::getBundledCaBundlePath(),
					'stream.certpath' => CaBundle::getBundledCaBundlePath(),
					'follow_location' => true,
					'timeout'         => 10,
					'userAgent'       => sprintf('Mcp4Joomla/%s', MCP4JOOMLA_VERSION),
					'headers'         => [
						// This is necessary for Joomla to process the request.
						'Accept'         => 'application/vnd.api+json',
						// This works on most servers
						'Authorization'  => sprintf("Bearer %s", $c['env']['BEARER_TOKEN'] ?? 'invalid'),
						// This works on servers which don't have the PHP fix for auth headers in their config.
						'X-Joomla-Token' => $c['env']['BEARER_TOKEN'] ?? 'invalid',
					],
				]
			));
	}
}
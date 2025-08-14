<?php
/**
 * @package       joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license       AGPL-3.0-or-later
 */

namespace Dionysopoulos\Mcp4Joomla\Container;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class InputProvider implements ServiceProviderInterface
{
	public function register(Container $pimple)
	{
		$pimple['input'] = fn(Container $c) => $c['optionsParser']->parse();
	}
}
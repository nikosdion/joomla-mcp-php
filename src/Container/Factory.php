<?php
/**
 * @package   joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license   AGPL-3.0-or-later
 */

namespace Dionysopoulos\Mcp4Joomla\Container;

use Pimple\Psr11\Container as Psr11Container;

final class Factory
{
	private static Psr11Container $container;

	private function __construct() {}

	public static function getContainer(): Psr11Container
	{
		if (!isset(self::$container))
		{
			self::$container = new Psr11Container(new Container());
		}

		return self::$container;
	}
}
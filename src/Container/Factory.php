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
	private static ?Psr11Container $container = null;

	private function __construct() {}

	public static function getContainer(): Psr11Container
	{
		if (self::$container === null)
		{
			self::$container = new Psr11Container(new Container());
		}

		return self::$container;
	}

	/**
	 * Replace the container instance. Intended for testing only.
	 *
	 * @param   Psr11Container  $container
	 *
	 * @return  void
	 * @internal
	 */
	public static function setContainer(Psr11Container $container): void
	{
		self::$container = $container;
	}

	/**
	 * Clear the container instance so the next getContainer() call creates a fresh one.
	 * Intended for testing only.
	 *
	 * @return  void
	 * @internal
	 */
	public static function reset(): void
	{
		self::$container = null;
	}
}
#!/usr/bin/env php
<?php
/**
 * @package   joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license   AGPL-3.0-or-later
 */

declare(strict_types=1);

use Dionysopoulos\Mcp4Joomla\Container\Factory;
use PhpMcp\Server\Server;
use PhpMcp\Server\Transports\StdioServerTransport;

// Load the Composer autoloader.
$autoloaderFile = __DIR__ . '/vendor/autoload.php';
$versionFile    = __DIR__ . '/version.php';

if (!file_exists($autoloaderFile))
{
	echo <<< END
********************************************************************************
 _____
| ____|_ __ _ __ ___  _ __
|  _| | '__| '__/ _ \| '__|
| |___| |  | | | (_) | |
|_____|_|  |_|  \___/|_|
********************************************************************************

You must initialise Composer dependencies before running this script.

--------------------------------------------------------------------------------
HOW TO FIX
--------------------------------------------------------------------------------

Go into the joomla-mcp-php working copy directory and run:

composer install


END;

	exit(255);
}

require_once $autoloaderFile;

// Load the version file if it exists.
if (file_exists($versionFile))
{
	require_once $versionFile;
}
else
{
	define('MCP4JOOMLA_VERSION', '0.0.0-dev');
}

// Start the MCP server.
try
{
	// Load the environment variables.
	Factory::getContainer()->get('env')->safeLoad();

	$server = Server::make()
			->withServerInfo('MCP4Joomla Server', MCP4JOOMLA_VERSION)
			->build();

	$server->discover(
			basePath: __DIR__,
			scanDirs: ['src/Server']
	);

	$transport = new StdioServerTransport();
	$server->listen($transport);

}
catch (\Throwable $e)
{
	fwrite(STDERR, "[CRITICAL ERROR] " . $e->getMessage() . "\n");
	exit(1);
}
#!/usr/bin/env php
<?php
/**
 * @package   joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license   AGPL-3.0-or-later
 */

declare(strict_types=1);

use Dionysopoulos\Mcp4Joomla\Container\Factory;
use Dionysopoulos\Mcp4Joomla\Utility\MinimalSchemaGenerator;
use PhpMcp\Server\Server;
use PhpMcp\Server\Transports\StdioServerTransport;
use PhpMcp\Server\Utils\Discoverer;
use PhpMcp\Server\Utils\DocBlockParser;

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

// Handle list-tools command early, before touching the DI container (which validates env vars).
if (isset($argv[1]) && $argv[1] === 'list-tools')
{
	$serverDir = __DIR__ . '/src/Server';
	$dirs      = array_filter(
		scandir($serverDir),
		fn($d) => is_dir("$serverDir/$d") && $d !== '.' && $d !== '..'
	);
	sort($dirs);

	foreach ($dirs as $category)
	{
		$server = Server::make()
			->withServerInfo('MCP4Joomla', MCP4JOOMLA_VERSION)
			->build();

		$server->discover(
			basePath: __DIR__,
			scanDirs: ["src/Server/$category"]
		);

		$tools     = $server->getRegistry()->getTools();
		$toolNames = array_keys($tools);
		sort($toolNames);

		if (empty($toolNames))
		{
			continue;
		}

		echo $category . "\n";

		foreach ($toolNames as $name)
		{
			echo "  $name\n";
		}
	}

	exit(0);
}

// Start the MCP server.
try
{
	// Get the container.
	$container = Factory::getContainer();

	/** @var \Monolog\Logger $log */
	$log = $container->get('log');
	$log->info('Starting MCP4Joomla server');

	// Load the environment variables.
	$env = $container->get('env');

	$log->debug('Environment variables loaded:');

	foreach ($env as $key => $value)
	{
		$log->debug("  $key = $value");
	}

	/** @var \DouglasGreen\OptParser\OptResult $input */
	$input = $container->get('input');

	// Determine scan directories based on --categories and --no-panopticon flags.
	$scanDirs    = ['src/Server'];
	$serverDir   = __DIR__ . '/src/Server';
	$allCatDirs  = array_filter(
		scandir($serverDir),
		fn($d) => is_dir("$serverDir/$d") && $d !== '.' && $d !== '..'
	);

	$categoriesParam = $input->categories;
	$noPanopticon    = $input->noPanopticon;

	if ($categoriesParam !== null)
	{
		$requestedCategories = array_map('trim', explode(',', $categoriesParam));
		$scanDirs            = [];

		// Build a case-insensitive map of directory names.
		$dirMap = [];

		foreach ($allCatDirs as $dir)
		{
			$dirMap[strtolower($dir)] = $dir;
		}

		foreach ($requestedCategories as $cat)
		{
			$key = strtolower($cat);

			if (isset($dirMap[$key]))
			{
				$scanDirs[] = "src/Server/{$dirMap[$key]}";
			}
			else
			{
				$log->warning("Category not found: $cat");
			}
		}

		if (empty($scanDirs))
		{
			fwrite(STDERR, "[ERROR] No valid categories found. Use 'list-tools' to see available categories.\n");
			exit(1);
		}
	}

	if ($noPanopticon)
	{
		if ($scanDirs === ['src/Server'])
		{
			// Expand to all individual dirs minus Panopticon.
			$scanDirs = [];

			foreach ($allCatDirs as $dir)
			{
				if (strtolower($dir) !== 'panopticon')
				{
					$scanDirs[] = "src/Server/$dir";
				}
			}
		}
		else
		{
			// Remove Panopticon from the list (case-insensitive).
			$scanDirs = array_values(array_filter(
				$scanDirs,
				fn($d) => strtolower(basename($d)) !== 'panopticon'
			));
		}
	}

	$server = Server::make()
		->withServerInfo('MCP4Joomla Server', MCP4JOOMLA_VERSION)
		->build();

	// If --no-schema is set, create a custom Discoverer with MinimalSchemaGenerator.
	$discoverer = null;

	if ($input->noSchema)
	{
		$docBlockParser = new DocBlockParser($log);
		$schemaGen      = new MinimalSchemaGenerator($docBlockParser);
		$discoverer     = new Discoverer($server->getRegistry(), $log, $docBlockParser, $schemaGen);
	}

	$server->discover(
		basePath: __DIR__,
		scanDirs: $scanDirs,
		discoverer: $discoverer
	);

	// If --tools is set, filter the Registry to only include the specified tools.
	$toolsParam = $input->tools;

	if ($toolsParam !== null)
	{
		$allowedTools = array_map('trim', explode(',', $toolsParam));
		$allowedTools = array_map('strtolower', $allowedTools);
		$registry     = $server->getRegistry();

		// Use reflection to access the private $tools property.
		$ref  = new \ReflectionClass($registry);
		$prop = $ref->getProperty('tools');
		$prop->setAccessible(true);
		$tools    = $prop->getValue($registry);
		$filtered = array_filter(
			$tools,
			fn($key) => in_array(strtolower($key), $allowedTools, true),
			ARRAY_FILTER_USE_KEY
		);
		$prop->setValue($registry, $filtered);

		$removedCount = count($tools) - count($filtered);
		$log->info("Tool filter applied: {$removedCount} tools removed, " . count($filtered) . " tools remaining.");
	}

	$transport = new StdioServerTransport();
	$server->listen($transport);

	$log->info('Stopping MCP4Joomla server');
}
catch (\Throwable $e)
{
	fwrite(STDERR, "[CRITICAL ERROR] " . $e->getMessage() . "\n");
	exit(1);
}

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

/**
 * Fix tool input schemas to be compatible with JSON Schema draft 2020-12.
 *
 * The php-mcp/server schema generator uses array-valued "type" fields (e.g. ["null", "string"])
 * for nullable parameters. This is valid in draft-07 but rejected by Claude's API which requires
 * draft 2020-12. This function rewrites those to use "anyOf" instead.
 *
 * @param   \PhpMcp\Server\Server  $server  The MCP server whose tool schemas should be fixed.
 *
 * @return  void
 */
function fixToolSchemas(\PhpMcp\Server\Server $server): void
{
	$registry = $server->getRegistry();
	$ref      = new \ReflectionClass($registry);
	$prop     = $ref->getProperty('tools');
	$prop->setAccessible(true);
	$tools = $prop->getValue($registry);

	$fixed = [];

	foreach ($tools as $name => $registeredTool)
	{
		$tool   = $registeredTool->schema;
		$schema = $tool->inputSchema;
		$changed = false;

		if (isset($schema['properties']))
		{
			foreach ($schema['properties'] as $pname => &$pval)
			{
				// Strip enum from non-string types (Google Gemini API only allows enum on strings).
				if (isset($pval['enum']) && isset($pval['type']) && is_string($pval['type']) && $pval['type'] !== 'string')
				{
					unset($pval['enum']);
					$changed = true;
				}

				// Fix draft-07 boolean exclusiveMinimum/exclusiveMaximum to draft 2020-12 numeric form.
				if (isset($pval['exclusiveMinimum']) && $pval['exclusiveMinimum'] === true && isset($pval['minimum']))
				{
					$pval['exclusiveMinimum'] = $pval['minimum'];
					unset($pval['minimum']);
					$changed = true;
				}

				if (isset($pval['exclusiveMaximum']) && $pval['exclusiveMaximum'] === true && isset($pval['maximum']))
				{
					$pval['exclusiveMaximum'] = $pval['maximum'];
					unset($pval['maximum']);
					$changed = true;
				}

				if (isset($pval['type']) && is_array($pval['type']))
				{
					$types = $pval['type'];

					// Remove "null" from the list; handle it via anyOf.
					$nonNullTypes = array_values(array_filter($types, fn($t) => $t !== 'null'));
					$hasNull      = count($nonNullTypes) < count($types);

					if ($hasNull && count($nonNullTypes) === 1)
					{
						// Common case: ["null", "string"] → anyOf with null
						$mainSchema = ['type' => $nonNullTypes[0]];

						// Move validation keywords to the main schema
						$keywords = [
							'description', 'pattern', 'format',
							'items', 'minItems', 'maxItems', 'uniqueItems',
							'minimum', 'maximum', 'exclusiveMinimum', 'exclusiveMaximum',
							'minLength', 'maxLength',
							'properties', 'required', 'additionalProperties',
						];

						foreach ($keywords as $key)
						{
							if (isset($pval[$key]))
							{
								$mainSchema[$key] = $pval[$key];
							}
						}

						// Only keep enum for string types (Google Gemini API restriction).
						if (isset($pval['enum']) && $nonNullTypes[0] === 'string')
						{
							$nonNullEnums = array_values(array_filter($pval['enum'], fn($v) => $v !== null));

							if (!empty($nonNullEnums))
							{
								$mainSchema['enum'] = $nonNullEnums;
							}
						}

						// For integer types, also accept numeric strings. Some MCP clients
						// (e.g. LLMs) send integers as JSON strings ("20" instead of 20).
						// The library's castToInt() will coerce the string to int after
						// schema validation passes.
						$anyOfBranches = [$mainSchema];

						if ($nonNullTypes[0] === 'integer')
						{
							$anyOfBranches[] = ['type' => 'string', 'pattern' => '^-?[0-9]+$'];
						}

						$anyOfBranches[] = ['type' => 'null'];

						$newPval = [
							'anyOf' => $anyOfBranches,
						];

						// Preserve default and description at the top level.
						if (array_key_exists('default', $pval))
						{
							$newPval['default'] = $pval['default'];
						}

						if (isset($pval['description']))
						{
							$newPval['description'] = $pval['description'];
						}

						$pval    = $newPval;
						$changed = true;
					}
					elseif (!$hasNull && count($nonNullTypes) === 1)
					{
						// Single non-null type in array: just simplify.
						$pval['type'] = $nonNullTypes[0];
						$changed      = true;
					}
				}
			}
			unset($pval);
		}

		if ($changed)
		{
			$newTool = new \PhpMcp\Schema\Tool(
				name: $tool->name,
				inputSchema: $schema,
				description: $tool->description,
				annotations: $tool->annotations,
			);

			$fixed[$name] = new \PhpMcp\Server\Elements\RegisteredTool(
				schema: $newTool,
				handler: $registeredTool->handler,
				isManual: $registeredTool->isManual,
			);
		}
		else
		{
			$fixed[$name] = $registeredTool;
		}
	}

	$prop->setValue($registry, $fixed);
}

/**
 * Recursively loads all PHP files from a directory so their classes become available for discovery.
 *
 * @param   string  $userCodeDir  Absolute path to the user code directory.
 *
 * @return  void
 */
function loadUserCodeFiles(string $userCodeDir): void
{
	$iterator = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator($userCodeDir, RecursiveDirectoryIterator::SKIP_DOTS)
	);

	foreach ($iterator as $file)
	{
		if ($file->getExtension() !== 'php')
		{
			continue;
		}

		try
		{
			require_once $file->getPathname();
		}
		catch (\Throwable $e)
		{
			fwrite(STDERR, "[WARNING] Failed to load user code file {$file->getPathname()}: {$e->getMessage()}\n");
		}
	}
}

// Resolve the user_code directory path.
$userCodeDir = \Phar::running() !== '' ? getcwd() . '/user_code' : __DIR__ . '/user_code';

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

	// Discover user code tools.
	if (is_dir($userCodeDir))
	{
		loadUserCodeFiles($userCodeDir);

		$server = Server::make()
			->withServerInfo('MCP4Joomla', MCP4JOOMLA_VERSION)
			->build();

		$server->discover(
			basePath: $userCodeDir,
			scanDirs: ['.']
		);

		$tools     = $server->getRegistry()->getTools();
		$toolNames = array_keys($tools);
		sort($toolNames);

		if (!empty($toolNames))
		{
			echo "UserCode\n";

			foreach ($toolNames as $name)
			{
				echo "  $name\n";
			}
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

	/** @var \Dionysopoulos\Mcp4Joomla\Cli\CliInput $input */
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
	$noAts           = $input->noAts;

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

	if ($noAts)
	{
		if ($scanDirs === ['src/Server'])
		{
			// Expand to all individual dirs minus Tickets.
			$scanDirs = [];

			foreach ($allCatDirs as $dir)
			{
				if (strtolower($dir) !== 'tickets')
				{
					$scanDirs[] = "src/Server/$dir";
				}
			}
		}
		else
		{
			// Remove Tickets from the list (case-insensitive).
			$scanDirs = array_values(array_filter(
				$scanDirs,
				fn($d) => strtolower(basename($d)) !== 'tickets'
			));
		}
	}

	$server = Server::make()
		->withServerInfo('MCP4Joomla Server', MCP4JOOMLA_VERSION)
		->withInstructions(<<<'INSTRUCTIONS'
MCP4Joomla provides tools for managing a Joomla CMS website via its Web Services API. Use these tools whenever the user asks about their Joomla site.

Tool categories and when to search for them:
- Content: articles and content categories (content_articles_*, content_categories_*). Use for creating, listing, reading, updating, or deleting articles and their categories.
- Banners: banners, banner categories, and banner clients (banners_*, banners_categories_*, banners_clients_*). Use for managing advertising banners.
- Config: application and component configuration (config_application_*, config_component_*). Use for reading or changing Joomla settings.
- Contact: contacts and contact categories (contact_*, contact_categories_*). Use for managing contact entries and submitting contact forms.
- ContentHistory: content version history (contenthistory_*). Use for viewing or managing revision history of content items.
- Fields: custom fields and field groups (fields_*, fields_groups_*). Use for managing custom fields on articles, contacts, etc.
- Installer: installed extensions (installer_extensions_*). Use for listing Joomla extensions (components, modules, plugins).
- JoomlaUpdate: Joomla core updates (joomlaupdate_*). Use for checking, preparing, and applying Joomla core updates.
- Languages: content languages, language overrides, and language packages (languages_content_*, languages_overrides_*, languages_packages_*). Use for managing multilingual content and translation overrides.
- Media: media files and adapters (media_files_*, media_adapters_*). Use for uploading, listing, or deleting images and other media files.
- Menus: site and administrator menus and menu items (menus_sitemenus_*, menus_siteitems_*, menus_adminmenus_*, menus_adminitems_*). Use for managing navigation menus.
- Messages: private messages (messages_*). Use for sending and managing private messages between Joomla users.
- Modules: site and administrator modules (modules_site_*, modules_admin_*). Use for managing sidebar widgets, footers, and other module positions.
- Newsfeeds: newsfeeds and newsfeed categories (newsfeeds_*, newsfeeds_categories_*). Use for managing RSS/Atom feed aggregation.
- Panopticon: Panopticon Connector tools for remote site management (panopticon_*). Use for extension updates, backups, security scans, and update site management via Akeeba Panopticon.
- Tickets: Akeeba Ticket System support tickets, posts, attachments, and manager notes (tickets_*). Use for managing ATS support tickets, reading ticket conversations, and inspecting attachments. Attachment and manager-note tools require ATS Pro.
- Plugins: Joomla plugins (plugins_*). Use for listing, reading, or updating plugin settings and state.
- Privacy: privacy requests and consents (privacy_requests_*, privacy_consents_*). Use for GDPR privacy management.
- Redirects: URL redirects (redirects_*). Use for managing 301/302 URL redirects.
- Tags: content tags (tags_*). Use for managing tags applied to articles and other content.
- Templates: site and administrator template styles (templates_sitestyles_*, templates_adminstyles_*). Use for managing template style configurations.
- Users: users, user groups, and viewing access levels (users_*, users_groups_*, users_levels_*). Use for managing user accounts and permissions.
INSTRUCTIONS
		)
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

	// Discover user code tools.
	if (is_dir($userCodeDir))
	{
		loadUserCodeFiles($userCodeDir);

		$docBlockParser   = $docBlockParser ?? new DocBlockParser($log);
		$userSchemaGen    = $input->noSchema
			? new MinimalSchemaGenerator($docBlockParser)
			: null;
		$userDiscoverer   = new Discoverer(
			$server->getRegistry(),
			$log,
			$docBlockParser,
			$userSchemaGen
		);

		$userDiscoverer->discover($userCodeDir, ['.']);

		$log->info('User code discovery complete.');
	}

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

	// If --non-destructive is set, keep only tools with readOnlyHint === true.
	if ($input->nonDestructive)
	{
		$registry = $server->getRegistry();
		$ref      = new \ReflectionClass($registry);
		$prop     = $ref->getProperty('tools');
		$prop->setAccessible(true);
		$tools    = $prop->getValue($registry);
		$filtered = array_filter(
			$tools,
			fn($registeredTool) => $registeredTool->schema->annotations?->readOnlyHint === true
		);
		$prop->setValue($registry, $filtered);

		$removedCount = count($tools) - count($filtered);
		$log->info("Non-destructive filter applied: {$removedCount} tools removed, " . count($filtered) . " read-only tools remaining.");
	}

	// Fix tool schemas to be compatible with JSON Schema draft 2020-12.
	fixToolSchemas($server);

	$transport = new StdioServerTransport();
	$server->listen($transport);

	$log->info('Stopping MCP4Joomla server');
}
catch (\Throwable $e)
{
	fwrite(STDERR, "[CRITICAL ERROR] " . $e->getMessage() . "\n");
	exit(1);
}

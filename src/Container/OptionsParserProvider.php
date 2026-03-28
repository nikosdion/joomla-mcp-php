<?php
/**
 * @package       joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license       AGPL-3.0-or-later
 */

namespace Dionysopoulos\Mcp4Joomla\Container;

use Pimple\Container;
use Pimple\ServiceProviderInterface;

class OptionsParserProvider implements ServiceProviderInterface
{
	public function register(Container $pimple)
	{
		$pimple['optionsParser'] = function (Container $c) {
			$optParser = new \Dionysopoulos\Mcp4Joomla\Cli\CliParser(
				'MCP4Joomla',
				'An MCP server for Joomla 5 and later, written in PHP.'
			);

			$optParser
				->addCommand(['server'], 'Start the MCP server.')
				->addCommand(['list-tools'], 'List all available tool categories and tools.')
				->addFlag(['debug', 'd'], 'Enable debug mode.')
				->addParam(['log', 'l'], 'OUTFILE', 'Log file path.')
				->addFlag(['no-panopticon'], 'Exclude all Panopticon tools.')
				->addFlag(['no-schema'], 'Strip Schema descriptions/constraints from tool input schemas.')
				->addParam(['categories', 'c'], 'STRING', 'Comma-separated category names to include.')
				->addParam(['tools', 't'], 'STRING', 'Comma-separated tool names to include.')
				->addFlag(['non-destructive', 'r'], 'Only expose read-only tools (no create, update, or delete).');

			$optParser
				->addUsage('server', ['debug', 'log', 'no-panopticon', 'no-schema', 'categories', 'tools', 'non-destructive'])
				->addUsage('list-tools', []);

			return $optParser;
		};
	}
}

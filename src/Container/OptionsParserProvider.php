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
			$optParser = new \DouglasGreen\OptParser\OptParser(
				'MCP4Joomla',
				'An MCP server for Joomla 5 and later, written in PHP.'
			);

			$optParser
				->addCommand(['server'], 'Start the MCP server.')
				->addFlag(['debug', 'd'], 'Enable debug mode.')
				->addParam(['log', 'l'], 'OUTFILE', 'Log file path.');

			$optParser->addUsageAll();

			return $optParser;
		};
	}
}
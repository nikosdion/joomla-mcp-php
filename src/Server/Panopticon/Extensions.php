<?php
/**
 * @package       joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license       AGPL-3.0-or-later
 */

namespace Dionysopoulos\Mcp4Joomla\Server\Panopticon;

use Dionysopoulos\Mcp4Joomla\Container\Factory;
use Dionysopoulos\Mcp4Joomla\Utility\AutoLoggingTrait;
use Dionysopoulos\Mcp4Joomla\Utility\GetDataFromResponseTrait;
use Dionysopoulos\Mcp4Joomla\Utility\HandleJoomlaAPIErrorTrait;
use Dionysopoulos\Mcp4Joomla\Utility\HttpDecorator;
use Dionysopoulos\Mcp4Joomla\Utility\VarToLogTrait;
use PhpMcp\Schema\ToolAnnotations;
use PhpMcp\Server\Attributes\McpTool;
use PhpMcp\Server\Attributes\Schema;

class Extensions
{
	use HandleJoomlaAPIErrorTrait;
	use GetDataFromResponseTrait;
	use VarToLogTrait;
	use AutoLoggingTrait;

	#[McpTool(
		name: 'panopticon_extensions_list',
		description: 'List installed extensions',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function listExtensions(
		#[Schema(description: 'Search filter for extensions')]
		?string $filterSearch = null,
		#[Schema(description: 'Filter by extension type')]
		?string $filterType = null
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/panopticon/extensions');

		if ($filterSearch !== null)
		{
			$uri->setVar('filter[search]', $filterSearch);
		}

		if ($filterType !== null)
		{
			$uri->setVar('filter[type]', $filterType);
		}

		$response = $http->get($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'extensions');
	}

	#[McpTool(
		name: 'panopticon_extensions_read',
		description: 'Read details of a specific extension',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function readExtension(
		#[Schema(description: 'The ID of the extension to retrieve')]
		int $id
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/panopticon/extensions/' . $id);
		$response = $http->get($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'extensions');
	}

	#[McpTool(
		name: 'panopticon_extensions_install',
		description: 'Install an extension from a URL'
	)]
	public function installExtension(
		#[Schema(description: 'Package URL to install')]
		string $url,
		#[Schema(description: 'Install type')]
		?string $type = null
	)
	{
		$this->autologMCPTool();

		$postData = [
			'url'  => $url,
			'type' => $type,
		];

		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/panopticon/extensions');

		$response = $http->post($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'extensions');
	}
}

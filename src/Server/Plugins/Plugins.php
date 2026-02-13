<?php
/**
 * @package       joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license       AGPL-3.0-or-later
 */

namespace Dionysopoulos\Mcp4Joomla\Server\Plugins;

use Dionysopoulos\Mcp4Joomla\Container\Factory;
use Dionysopoulos\Mcp4Joomla\Utility\AutoLoggingTrait;
use Dionysopoulos\Mcp4Joomla\Utility\GetDataFromResponseTrait;
use Dionysopoulos\Mcp4Joomla\Utility\HandleJoomlaAPIErrorTrait;
use Dionysopoulos\Mcp4Joomla\Utility\HttpDecorator;
use Dionysopoulos\Mcp4Joomla\Utility\VarToLogTrait;
use PhpMcp\Schema\ToolAnnotations;
use PhpMcp\Server\Attributes\McpTool;

/**
 * MCP elements for Joomla! plugin management
 */
class Plugins
{
	use HandleJoomlaAPIErrorTrait;
	use GetDataFromResponseTrait;
	use VarToLogTrait;
	use AutoLoggingTrait;

	#[McpTool(
		name: 'plugins_list',
		description: 'List existing plugins',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function listPlugins(
		#[Schema(description: 'Search plugins by name')]
		?string $filterSearch = null,
		#[Schema(description: 'Filter by enabled state: 0=disabled, 1=enabled', enum: [0, 1])]
		?int $filterState = null,
		#[Schema(description: 'Filter by plugin group (folder)')]
		?string $filterFolder = null,
		#[Schema(description: 'Filter by plugin element name')]
		?string $filterElement = null
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/plugins');

		if ($filterSearch !== null)
		{
			$uri->setVar('filter[search]', $filterSearch);
		}

		if ($filterState !== null)
		{
			$uri->setVar('filter[enabled]', $filterState);
		}

		if ($filterFolder !== null)
		{
			$uri->setVar('filter[folder]', $filterFolder);
		}

		if ($filterElement !== null)
		{
			$uri->setVar('filter[element]', $filterElement);
		}

		$response = $http->get($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'plugins');
	}

	#[McpTool(
		name: 'plugins_read',
		description: 'Retrieve the information of the specified plugin',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function readPlugin(
		#[Schema(description: 'The ID of the plugin to retrieve')]
		int $id
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/plugins/' . $id);
		$response = $http->get($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'plugins');
	}

	#[McpTool(
		name: 'plugins_update',
		description: 'Update an existing plugin',
		annotations: new ToolAnnotations(idempotentHint: true)
	)]
	public function updatePlugin(
		#[Schema(description: 'The ID of the plugin to update')]
		int $id,
		#[Schema(description: 'Enable or disable the plugin: 0=disabled, 1=enabled', enum: [0, 1])]
		?int $enabled = null,
		#[Schema(description: 'The viewing access level for the plugin')]
		?int $access = null,
		#[Schema(description: 'The ordering of the plugin within its group')]
		?int $ordering = null,
		#[Schema(description: 'JSON string of plugin parameters')]
		?string $params = null
	)
	{
		$this->autologMCPTool();

		$postData = [
			'enabled'  => $enabled,
			'access'   => $access,
			'ordering' => $ordering,
			'params'   => $params,
		];

		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/plugins/' . $id);

		$response = $http->patch($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'plugins');
	}
}

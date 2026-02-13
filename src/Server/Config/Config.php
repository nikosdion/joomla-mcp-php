<?php
/**
 * @package       joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license       AGPL-3.0-or-later
 */

namespace Dionysopoulos\Mcp4Joomla\Server\Config;

use Dionysopoulos\Mcp4Joomla\Container\Factory;
use Dionysopoulos\Mcp4Joomla\Utility\AutoLoggingTrait;
use Dionysopoulos\Mcp4Joomla\Utility\GetDataFromResponseTrait;
use Dionysopoulos\Mcp4Joomla\Utility\HandleJoomlaAPIErrorTrait;
use Dionysopoulos\Mcp4Joomla\Utility\HttpDecorator;
use Dionysopoulos\Mcp4Joomla\Utility\VarToLogTrait;
use PhpMcp\Schema\ToolAnnotations;
use PhpMcp\Server\Attributes\McpTool;

/**
 * MCP elements for Joomla! configuration management
 */
class Config
{
	use HandleJoomlaAPIErrorTrait;
	use GetDataFromResponseTrait;
	use VarToLogTrait;
	use AutoLoggingTrait;

	#[McpTool(
		name: 'config_application_read',
		description: 'Read the Joomla application configuration',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function readApplicationConfig()
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/config/application');
		$response = $http->get($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'application');
	}

	#[McpTool(
		name: 'config_application_update',
		description: 'Update the Joomla application configuration',
		annotations: new ToolAnnotations(idempotentHint: true)
	)]
	public function updateApplicationConfig(
		#[Schema(description: 'JSON string of configuration key-value pairs to update')]
		string $configData
	)
	{
		$this->autologMCPTool();

		$postData = json_decode($configData, true);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/config/application');

		$response = $http->patch($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'application');
	}

	#[McpTool(
		name: 'config_component_read',
		description: 'Read the configuration of a Joomla component',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function readComponentConfig(
		#[Schema(description: 'The component name, e.g. "com_content"')]
		string $componentName
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/config/' . $componentName);
		$response = $http->get($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'component');
	}

	#[McpTool(
		name: 'config_component_update',
		description: 'Update the configuration of a Joomla component',
		annotations: new ToolAnnotations(idempotentHint: true)
	)]
	public function updateComponentConfig(
		#[Schema(description: 'The component name, e.g. "com_content"')]
		string $componentName,
		#[Schema(description: 'JSON string of configuration key-value pairs to update')]
		string $configData
	)
	{
		$this->autologMCPTool();

		$postData = json_decode($configData, true);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/config/' . $componentName);

		$response = $http->patch($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'component');
	}
}

<?php
/**
 * @package       joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license       AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Dionysopoulos\Mcp4Joomla\Server\Config;

use Dionysopoulos\Mcp4Joomla\Container\Factory;
use Dionysopoulos\Mcp4Joomla\Utility\AutoLoggingTrait;
use Dionysopoulos\Mcp4Joomla\Utility\GetDataFromResponseTrait;
use Dionysopoulos\Mcp4Joomla\Utility\HandleJoomlaAPIErrorTrait;
use Dionysopoulos\Mcp4Joomla\Utility\HttpDecorator;
use Dionysopoulos\Mcp4Joomla\Utility\JsonInputCompatibilityTrait;
use Dionysopoulos\Mcp4Joomla\Utility\ReadMergeUpdateTrait;
use Dionysopoulos\Mcp4Joomla\Utility\VarToLogTrait;
use PhpMcp\Schema\ToolAnnotations;
use PhpMcp\Server\Attributes\McpTool;
use PhpMcp\Server\Attributes\Schema;

/**
 * MCP elements for Joomla! configuration management
 */
class Config
{
	use HandleJoomlaAPIErrorTrait;
	use GetDataFromResponseTrait;
	use VarToLogTrait;
	use AutoLoggingTrait;
	use ReadMergeUpdateTrait;
	use JsonInputCompatibilityTrait;

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
		$response = $http->get($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'application');
	}

	#[McpTool(
		name: 'config_application_update',
		description: 'Update the Joomla application configuration',
		annotations: new ToolAnnotations(idempotentHint: true)
	)]
	public function updateApplicationConfig(
		#[Schema(description: 'Configuration key-value pairs as a JSON string or object')]
		array|string $configData
	)
	{
		$this->autologMCPTool();

		$postData = $this->normaliseJsonObjectInput($configData, 'configData');

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/config/application');

		$postData = $this->prepareReadMergeUpdatePayloadRecursive($http, (string) $uri, 'application', $postData);

		$response = $http->patch($uri->toString(), json_encode($postData), ['Content-Type' => 'application/json']);

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
		$response = $http->get($uri->toString());

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
		#[Schema(description: 'Configuration key-value pairs as a JSON string or object')]
		array|string $configData
	)
	{
		$this->autologMCPTool();

		$postData = $this->normaliseJsonObjectInput($configData, 'configData');

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/config/' . $componentName);

		$postData = $this->prepareReadMergeUpdatePayloadRecursive($http, (string) $uri, 'component', $postData);

		$response = $http->patch($uri->toString(), json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'component');
	}
}

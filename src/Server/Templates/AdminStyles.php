<?php
/**
 * @package       joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license       AGPL-3.0-or-later
 */

namespace Dionysopoulos\Mcp4Joomla\Server\Templates;

use Dionysopoulos\Mcp4Joomla\Container\Factory;
use Dionysopoulos\Mcp4Joomla\Utility\AutoLoggingTrait;
use Dionysopoulos\Mcp4Joomla\Utility\GetDataFromResponseTrait;
use Dionysopoulos\Mcp4Joomla\Utility\HandleJoomlaAPIErrorTrait;
use Dionysopoulos\Mcp4Joomla\Utility\HttpDecorator;
use Dionysopoulos\Mcp4Joomla\Utility\VarToLogTrait;
use PhpMcp\Schema\ToolAnnotations;
use PhpMcp\Server\Attributes\McpTool;

/**
 * MCP elements for Joomla! administrator template style management
 */
class AdminStyles
{
	use HandleJoomlaAPIErrorTrait;
	use GetDataFromResponseTrait;
	use VarToLogTrait;
	use AutoLoggingTrait;

	#[McpTool(
		name: 'templates_adminstyles_list',
		description: 'List existing administrator template styles',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function listStyles(
		#[Schema(description: 'Search administrator template styles by name')]
		?string $filterSearch = null,
		#[Schema(description: 'Filter by template name')]
		?string $filterTemplate = null
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/templates/styles/administrator');

		if ($filterSearch !== null)
		{
			$uri->setVar('filter[search]', $filterSearch);
		}

		if ($filterTemplate !== null)
		{
			$uri->setVar('filter[template]', $filterTemplate);
		}

		$response = $http->get($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'styles');
	}

	#[McpTool(
		name: 'templates_adminstyles_read',
		description: 'Retrieve the information of the specified administrator template style',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function readStyle(
		#[Schema(description: 'The ID of the administrator template style to retrieve')]
		int $id
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/templates/styles/administrator/' . $id);
		$response = $http->get($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'styles');
	}

	#[McpTool(
		name: 'templates_adminstyles_create',
		description: 'Create a new administrator template style'
	)]
	public function createStyle(
		#[Schema(description: 'The title of the template style')]
		string $title,
		#[Schema(description: 'The template name')]
		string $template,
		#[Schema(description: 'Whether this is the default administrator template style')]
		?bool $home = false,
		#[Schema(description: 'JSON string of template style parameters')]
		?string $params = null
	)
	{
		$this->autologMCPTool();

		$postData = [
			'title'    => $title,
			'template' => $template,
			'home'     => $home,
			'params'   => $params,
		];

		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/templates/styles/administrator');

		$response = $http->post($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'styles');
	}

	#[McpTool(
		name: 'templates_adminstyles_update',
		description: 'Update an existing administrator template style',
		annotations: new ToolAnnotations(idempotentHint: true)
	)]
	public function updateStyle(
		#[Schema(description: 'The ID of the administrator template style to update')]
		int $id,
		#[Schema(description: 'The title of the template style')]
		?string $title = null,
		#[Schema(description: 'The template name')]
		?string $template = null,
		#[Schema(description: 'Whether this is the default administrator template style')]
		?bool $home = null,
		#[Schema(description: 'JSON string of template style parameters')]
		?string $params = null
	)
	{
		$this->autologMCPTool();

		$postData = [
			'title'    => $title,
			'template' => $template,
			'home'     => $home,
			'params'   => $params,
		];

		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/templates/styles/administrator/' . $id);

		$response = $http->patch($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'styles');
	}

	#[McpTool(
		name: 'templates_adminstyles_delete',
		description: 'Delete an administrator template style',
		annotations: new ToolAnnotations(destructiveHint: true)
	)]
	public function deleteStyle(
		#[Schema(description: 'The ID of the administrator template style to delete')]
		int $id
	): bool
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/templates/styles/administrator/' . $id);
		$response = $http->delete($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $response->getStatusCode() === 204;
	}
}

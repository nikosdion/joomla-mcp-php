<?php
/**
 * @package       joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license       AGPL-3.0-or-later
 */

namespace Dionysopoulos\Mcp4Joomla\Server\Menus;

use Dionysopoulos\Mcp4Joomla\Container\Factory;
use Dionysopoulos\Mcp4Joomla\Utility\AutoLoggingTrait;
use Dionysopoulos\Mcp4Joomla\Utility\GetDataFromResponseTrait;
use Dionysopoulos\Mcp4Joomla\Utility\HandleJoomlaAPIErrorTrait;
use Dionysopoulos\Mcp4Joomla\Utility\HttpDecorator;
use Dionysopoulos\Mcp4Joomla\Utility\TitleToAliasTrait;
use Dionysopoulos\Mcp4Joomla\Utility\VarToLogTrait;
use PhpMcp\Schema\ToolAnnotations;
use PhpMcp\Server\Attributes\McpTool;
use PhpMcp\Server\Attributes\Schema;

/**
 * MCP elements for Joomla! administrator menus management
 */
class AdminMenus
{
	use HandleJoomlaAPIErrorTrait;
	use GetDataFromResponseTrait;
	use VarToLogTrait;
	use AutoLoggingTrait;
	use TitleToAliasTrait;

	#[McpTool(
		name: 'menus_adminmenus_list',
		description: 'List existing administrator menus',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function listMenus(
		#[Schema(description: 'Search menus by title')]
		?string $filterSearch = null
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/menus/administrator');

		if ($filterSearch !== null)
		{
			$uri->setVar('filter[search]', $filterSearch);
		}

		$response = $http->get($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'menus');
	}

	#[McpTool(
		name: 'menus_adminmenus_read',
		description: 'Retrieve the information of the specified administrator menu',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function readMenu(
		#[Schema(description: 'The ID of the menu to retrieve')]
		int $id
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/menus/administrator/' . $id);
		$response = $http->get($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'menus');
	}

	#[McpTool(
		name: 'menus_adminmenus_create',
		description: 'Create a new administrator menu'
	)]
	public function createMenu(
		#[Schema(description: 'Menu title', minLength: 1, maxLength: 255)]
		string $title,
		#[Schema(description: 'Unique menu type identifier', minLength: 1, maxLength: 255)]
		string $menutype,
		#[Schema(description: 'Menu description')]
		?string $description = null
	)
	{
		$this->autologMCPTool();

		$postData = [
			'title'       => $title,
			'menutype'    => $menutype,
			'description' => $description,
		];

		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/menus/administrator');

		$response = $http->post($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'menus');
	}

	#[McpTool(
		name: 'menus_adminmenus_update',
		description: 'Update an existing administrator menu',
		annotations: new ToolAnnotations(idempotentHint: true)
	)]
	public function updateMenu(
		#[Schema(description: 'The ID of the menu to modify')]
		int $id,
		#[Schema(description: 'Menu title', minLength: 1, maxLength: 255)]
		?string $title = null,
		#[Schema(description: 'Unique menu type identifier', minLength: 1, maxLength: 255)]
		?string $menutype = null,
		#[Schema(description: 'Menu description')]
		?string $description = null
	)
	{
		$this->autologMCPTool();

		$postData = [
			'title'       => $title,
			'menutype'    => $menutype,
			'description' => $description,
		];

		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/menus/administrator/' . $id);

		$response = $http->patch($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'menus');
	}

	#[McpTool(
		name: 'menus_adminmenus_delete',
		description: 'Permanently deletes an administrator menu.',
		annotations: new ToolAnnotations(destructiveHint: true)
	)]
	public function deleteMenu(
		#[Schema(description: 'The ID of the menu to delete')]
		int $id
	): bool
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/menus/administrator/' . $id);
		$response = $http->delete($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $response->getStatusCode() === 204;
	}
}

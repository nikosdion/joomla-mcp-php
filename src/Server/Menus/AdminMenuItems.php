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
 * MCP elements for Joomla! administrator menu items management
 */
class AdminMenuItems
{
	use HandleJoomlaAPIErrorTrait;
	use GetDataFromResponseTrait;
	use VarToLogTrait;
	use AutoLoggingTrait;
	use TitleToAliasTrait;

	#[McpTool(
		name: 'menus_adminitems_list',
		description: 'List existing administrator menu items',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function listMenuItems(
		#[Schema(description: 'Search menu items by title')]
		?string $filterSearch = null,
		#[Schema(
			description: 'Filter by publish state: 0=unpublished, 1=published, 2=archived, -2=trashed',
			enum: [null, 0, 1, 2, -2]
		)]
		?int $filterState = null,
		#[Schema(description: 'Filter by language code, or "*" for all languages')]
		?string $filterLanguage = null,
		#[Schema(description: 'Filter by menu type identifier')]
		?string $filterMenutype = null,
		#[Schema(description: 'Filter by menu item nesting level')]
		?int $filterLevel = null
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/menus/administrator/items');

		if ($filterSearch !== null)
		{
			$uri->setVar('filter[search]', $filterSearch);
		}

		if ($filterState !== null)
		{
			$uri->setVar('filter[published]', $filterState);
		}

		if ($filterLanguage !== null)
		{
			$uri->setVar('filter[language]', $filterLanguage);
		}

		if ($filterMenutype !== null)
		{
			$uri->setVar('filter[menutype]', $filterMenutype);
		}

		if ($filterLevel !== null)
		{
			$uri->setVar('filter[level]', $filterLevel);
		}

		$response = $http->get($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'menuitems');
	}

	#[McpTool(
		name: 'menus_adminitems_read',
		description: 'Retrieve the information of the specified administrator menu item',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function readMenuItem(
		#[Schema(description: 'The ID of the menu item to retrieve')]
		int $id
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/menus/administrator/items/' . $id);
		$response = $http->get($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'menuitems');
	}

	#[McpTool(
		name: 'menus_adminitems_create',
		description: 'Create a new administrator menu item'
	)]
	public function createMenuItem(
		#[Schema(description: 'Menu item title', minLength: 1, maxLength: 255)]
		string $title,
		#[Schema(description: 'URL slug for the menu item', pattern: '^[a-z0-9-_]+$')]
		?string $alias = null,
		#[Schema(description: 'Menu type identifier this item belongs to', minLength: 1)]
		string $menutype,
		#[Schema(description: 'Menu item type, e.g. "component"')]
		string $type,
		#[Schema(description: 'The link URL for the menu item')]
		string $link,
		#[Schema(description: 'Parent menu item ID, 1 for root')]
		?int $parentId = 1,
		#[Schema(
			description: 'Publish state: 0=unpublished, 1=published, 2=archived, -2=trashed',
			enum: [0, 1, 2, -2]
		)]
		int $published = 1,
		#[Schema(description: 'The Joomla! viewing access level for the menu item')]
		?int $access = null,
		#[Schema(description: 'Language code for the menu item, or "*" for all languages')]
		string $language = '*',
		#[Schema(description: 'The component ID associated with this menu item')]
		?int $componentId = null,
		#[Schema(description: 'Optional note for the menu item')]
		?string $note = null,
		#[Schema(description: 'An array of tag IDs to associate with the menu item', items: ['type' => 'integer'], minItems: 0, uniqueItems: true)]
		?array $tags = null,
		#[Schema(description: 'Browser navigation: 0=same window, 1=new window', enum: ['0', '1'])]
		?string $browserNav = null,
		#[Schema(description: 'Whether this menu item is the home page')]
		?bool $home = null
	)
	{
		$this->autologMCPTool();

		$postData = [
			'title'        => $title,
			'alias'        => $alias ?: $this->titleToAlias($title),
			'menutype'     => $menutype,
			'type'         => $type,
			'link'         => $link,
			'parent_id'    => $parentId,
			'published'    => $published,
			'access'       => $access ?? 1,
			'language'     => $language,
			'component_id' => $componentId,
			'note'         => $note,
			'tags'         => $tags,
			'browserNav'   => $browserNav,
			'home'         => $home,
		];

		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/menus/administrator/items');

		$response = $http->post($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'menuitems');
	}

	#[McpTool(
		name: 'menus_adminitems_update',
		description: 'Update an existing administrator menu item',
		annotations: new ToolAnnotations(idempotentHint: true)
	)]
	public function updateMenuItem(
		#[Schema(description: 'The ID of the menu item to modify')]
		int $id,
		#[Schema(description: 'Menu item title', minLength: 1, maxLength: 255)]
		?string $title = null,
		#[Schema(description: 'URL slug for the menu item', pattern: '^[a-z0-9-_]+$')]
		?string $alias = null,
		#[Schema(description: 'Menu type identifier this item belongs to')]
		?string $menutype = null,
		#[Schema(description: 'Menu item type, e.g. "component"')]
		?string $type = null,
		#[Schema(description: 'The link URL for the menu item')]
		?string $link = null,
		#[Schema(description: 'Parent menu item ID, 1 for root')]
		?int $parentId = null,
		#[Schema(
			description: 'Publish state: 0=unpublished, 1=published, 2=archived, -2=trashed',
			enum: [null, 0, 1, 2, -2]
		)]
		?int $published = null,
		#[Schema(description: 'The Joomla! viewing access level for the menu item')]
		?int $access = null,
		#[Schema(description: 'Language code for the menu item, or "*" for all languages')]
		?string $language = null,
		#[Schema(description: 'The component ID associated with this menu item')]
		?int $componentId = null,
		#[Schema(description: 'Optional note for the menu item')]
		?string $note = null,
		#[Schema(description: 'An array of tag IDs to associate with the menu item', items: ['type' => 'integer'], minItems: 0, uniqueItems: true)]
		?array $tags = null,
		#[Schema(description: 'Browser navigation: 0=same window, 1=new window', enum: ['0', '1'])]
		?string $browserNav = null,
		#[Schema(description: 'Whether this menu item is the home page')]
		?bool $home = null
	)
	{
		$this->autologMCPTool();

		$postData = [
			'title'        => $title,
			'alias'        => $alias,
			'menutype'     => $menutype,
			'type'         => $type,
			'link'         => $link,
			'parent_id'    => $parentId,
			'published'    => $published,
			'access'       => $access,
			'language'     => $language,
			'component_id' => $componentId,
			'note'         => $note,
			'tags'         => $tags,
			'browserNav'   => $browserNav,
			'home'         => $home,
		];

		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/menus/administrator/items/' . $id);

		$response = $http->patch($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'menuitems');
	}

	#[McpTool(
		name: 'menus_adminitems_delete',
		description: 'Permanently deletes an administrator menu item.',
		annotations: new ToolAnnotations(destructiveHint: true)
	)]
	public function deleteMenuItem(
		#[Schema(description: 'The ID of the menu item to delete')]
		int $id
	): bool
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/menus/administrator/items/' . $id);
		$response = $http->delete($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $response->getStatusCode() === 204;
	}

	#[McpTool(
		name: 'menus_adminitems_types',
		description: 'List available menu item types for administrator menus',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function listMenuItemTypes()
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/menus/administrator/items/types');
		$response = $http->get($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, null);
	}
}

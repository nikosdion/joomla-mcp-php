<?php
/**
 * @package       joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license       AGPL-3.0-or-later
 */

namespace Dionysopoulos\Mcp4Joomla\Server\Users;

use Dionysopoulos\Mcp4Joomla\Container\Factory;
use Dionysopoulos\Mcp4Joomla\Utility\AutoLoggingTrait;
use Dionysopoulos\Mcp4Joomla\Utility\GetDataFromResponseTrait;
use Dionysopoulos\Mcp4Joomla\Utility\HandleJoomlaAPIErrorTrait;
use Dionysopoulos\Mcp4Joomla\Utility\HttpDecorator;
use Dionysopoulos\Mcp4Joomla\Utility\ReadMergeUpdateTrait;
use Dionysopoulos\Mcp4Joomla\Utility\VarToLogTrait;
use PhpMcp\Schema\ToolAnnotations;
use PhpMcp\Server\Attributes\McpTool;
use PhpMcp\Server\Attributes\Schema;

/**
 * MCP elements for Joomla! user group management
 */
class Groups
{
	use HandleJoomlaAPIErrorTrait;
	use GetDataFromResponseTrait;
	use VarToLogTrait;
	use AutoLoggingTrait;
	use ReadMergeUpdateTrait;

	#[McpTool(
		name: 'users_groups_list',
		description: 'List existing user groups',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function listGroups(
		#[Schema(description: 'Search user groups by title')]
		?string $filterSearch = null,
		#[Schema(description: 'Maximum number of items to return per page', minimum: 1)]
		?int $pageLimit = null,
		#[Schema(description: 'Starting record offset for pagination (0-based)', minimum: 0)]
		?int $pageOffset = null
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/users/groups');

		if ($filterSearch !== null)
		{
			$uri->setVar('filter[search]', $filterSearch);
		}

		if ($pageLimit !== null)
		{
			$uri->setVar('page[limit]', $pageLimit);
		}

		if ($pageOffset !== null)
		{
			$uri->setVar('page[offset]', $pageOffset);
		}

		$response = $http->get($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'groups');
	}

	#[McpTool(
		name: 'users_groups_read',
		description: 'Retrieve the information of the specified user group',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function readGroup(
		#[Schema(description: 'The ID of the user group to retrieve')]
		int $id
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/users/groups/' . $id);
		$response = $http->get($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'groups');
	}

	#[McpTool(
		name: 'users_groups_create',
		description: 'Create a new user group'
	)]
	public function createGroup(
		#[Schema(description: 'Title of the user group', minLength: 1, maxLength: 255)]
		string $title,
		#[Schema(description: 'The ID of the parent user group', minimum: 1)]
		?int $parentId = 1
	)
	{
		$this->autologMCPTool();

		$postData = [
			'title'     => $title,
			'parent_id' => $parentId,
		];

		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/users/groups');

		$response = $http->post($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'groups');
	}

	#[McpTool(
		name: 'users_groups_update',
		description: 'Update an existing user group',
		annotations: new ToolAnnotations(idempotentHint: true)
	)]
	public function updateGroup(
		#[Schema(description: 'The ID of the user group to modify')]
		int $id,
		#[Schema(description: 'Title of the user group', minLength: 1, maxLength: 255)]
		?string $title = null,
		#[Schema(description: 'The ID of the parent user group', minimum: 1)]
		?int $parentId = null
	)
	{
		$this->autologMCPTool();

		$postData = [
			'title'     => $title,
			'parent_id' => $parentId,
		];

		$writableFields = array_keys($postData);
		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/users/groups/' . $id);

		$postData = $this->prepareReadMergeUpdatePayload($http, (string) $uri, 'groups', $postData, $writableFields);

		$response = $http->patch($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'groups');
	}

	#[McpTool(
		name: 'users_groups_delete',
		description: 'Permanently delete a user group',
		annotations: new ToolAnnotations(destructiveHint: true)
	)]
	public function deleteGroup(
		#[Schema(description: 'The ID of the user group to delete')]
		int $id
	): bool
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/users/groups/' . $id);
		$response = $http->delete($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $response->getStatusCode() === 204;
	}
}

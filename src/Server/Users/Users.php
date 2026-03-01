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
 * MCP elements for Joomla! user management
 */
class Users
{
	use HandleJoomlaAPIErrorTrait;
	use GetDataFromResponseTrait;
	use VarToLogTrait;
	use AutoLoggingTrait;
	use ReadMergeUpdateTrait;

	#[McpTool(
		name: 'users_list',
		description: 'List existing users',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function listUsers(
		#[Schema(description: 'Search users by name, username, or email')]
		?string $filterSearch = null,
		#[Schema(description: 'Filter by user state: 0=disabled, 1=enabled', enum: [0, 1])]
		?int $filterState = null,
		#[Schema(description: 'Filter by user group ID')]
		?int $filterGroup = null,
		#[Schema(description: 'Filter by registration date range')]
		?string $filterRange = null,
		#[Schema(description: 'Maximum number of items to return per page', minimum: 1)]
		?int $pageLimit = null,
		#[Schema(description: 'Starting record offset for pagination (0-based)', minimum: 0)]
		?int $pageOffset = null
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/users');

		if ($filterSearch !== null)
		{
			$uri->setVar('filter[search]', $filterSearch);
		}

		if ($filterState !== null)
		{
			$uri->setVar('filter[state]', $filterState);
		}

		if ($filterGroup !== null)
		{
			$uri->setVar('filter[group]', $filterGroup);
		}

		if ($filterRange !== null)
		{
			$uri->setVar('filter[range]', $filterRange);
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

		return $this->getDataFromResponse($response, 'users');
	}

	#[McpTool(
		name: 'users_read',
		description: 'Retrieve the information of the specified user',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function readUser(
		#[Schema(description: 'The ID of the user to retrieve')]
		int $id
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/users/' . $id);
		$response = $http->get($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'users');
	}

	#[McpTool(
		name: 'users_create',
		description: 'Create a new user'
	)]
	public function createUser(
		#[Schema(description: 'Full name of the user', minLength: 1, maxLength: 255)]
		string $name,
		#[Schema(description: 'Login username', minLength: 1, maxLength: 150)]
		string $username,
		#[Schema(description: 'Email address of the user', format: 'email')]
		string $email,
		#[Schema(description: 'Password for the user', minLength: 1)]
		string $password,
		#[Schema(description: 'Block status: 0=not blocked, 1=blocked', enum: [0, 1])]
		int $block = 0,
		#[Schema(description: 'Send an email notification to the user')]
		?bool $sendEmail = null,
		#[Schema(description: 'Require the user to reset their password on next login')]
		?bool $requireReset = null,
		#[Schema(description: 'An array of user group IDs to assign the user to', items: ['type' => 'integer'], minItems: 1)]
		?array $groups = null
	)
	{
		$this->autologMCPTool();

		$postData = [
			'name'         => $name,
			'username'     => $username,
			'email'        => $email,
			'password'     => $password,
			'block'        => $block,
			'sendEmail'    => $sendEmail,
			'requireReset' => $requireReset,
			'groups'       => $groups,
		];

		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/users');

		$response = $http->post($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'users');
	}

	#[McpTool(
		name: 'users_update',
		description: 'Update an existing user',
		annotations: new ToolAnnotations(idempotentHint: true)
	)]
	public function updateUser(
		#[Schema(description: 'The ID of the user to modify')]
		int $id,
		#[Schema(description: 'Full name of the user', minLength: 1, maxLength: 255)]
		?string $name = null,
		#[Schema(description: 'Login username', minLength: 1, maxLength: 150)]
		?string $username = null,
		#[Schema(description: 'Email address of the user', format: 'email')]
		?string $email = null,
		#[Schema(description: 'Password for the user', minLength: 1)]
		?string $password = null,
		#[Schema(description: 'Block status: 0=not blocked, 1=blocked', enum: [0, 1])]
		?int $block = null,
		#[Schema(description: 'Send an email notification to the user')]
		?bool $sendEmail = null,
		#[Schema(description: 'Require the user to reset their password on next login')]
		?bool $requireReset = null,
		#[Schema(description: 'An array of user group IDs to assign the user to', items: ['type' => 'integer'], minItems: 1)]
		?array $groups = null
	)
	{
		$this->autologMCPTool();

		$postData = [
			'name'         => $name,
			'username'     => $username,
			'email'        => $email,
			'password'     => $password,
			'block'        => $block,
			'sendEmail'    => $sendEmail,
			'requireReset' => $requireReset,
			'groups'       => $groups,
		];

		$writableFields = array_keys($postData);
		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/users/' . $id);

		$postData = $this->prepareReadMergeUpdatePayload($http, (string) $uri, 'users', $postData, $writableFields);

		$response = $http->patch($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'users');
	}

	#[McpTool(
		name: 'users_delete',
		description: 'Permanently delete a user',
		annotations: new ToolAnnotations(destructiveHint: true)
	)]
	public function deleteUser(
		#[Schema(description: 'The ID of the user to delete')]
		int $id
	): bool
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/users/' . $id);
		$response = $http->delete($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $response->getStatusCode() === 204;
	}
}

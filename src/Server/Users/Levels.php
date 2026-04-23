<?php
/**
 * @package       joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license       AGPL-3.0-or-later
 */

declare(strict_types=1);

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
 * MCP elements for Joomla! viewing access level management
 */
class Levels
{
	use HandleJoomlaAPIErrorTrait;
	use GetDataFromResponseTrait;
	use VarToLogTrait;
	use AutoLoggingTrait;
	use ReadMergeUpdateTrait;

	#[McpTool(
		name: 'users_levels_list',
		description: 'List existing viewing access levels',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function listLevels(
		#[Schema(description: 'Search viewing access levels by title')]
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
		$uri  = $http->getUri('v1/users/levels');

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

		return $this->getDataFromResponse($response, 'levels');
	}

	#[McpTool(
		name: 'users_levels_read',
		description: 'Retrieve the information of the specified viewing access level',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function readLevel(
		#[Schema(description: 'The ID of the viewing access level to retrieve')]
		int $id
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/users/levels/' . $id);
		$response = $http->get($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'levels');
	}

	#[McpTool(
		name: 'users_levels_create',
		description: 'Create a new viewing access level'
	)]
	public function createLevel(
		#[Schema(description: 'Title of the viewing access level', minLength: 1, maxLength: 255)]
		string $title,
		#[Schema(description: 'An array of user group IDs that can access this level', items: ['type' => 'integer'], minItems: 1)]
		array $rules
	)
	{
		$this->autologMCPTool();

		$postData = [
			'title' => $title,
			'rules' => $rules,
		];

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/users/levels');

		$response = $http->post($uri->toString(), json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'levels');
	}

	#[McpTool(
		name: 'users_levels_update',
		description: 'Update an existing viewing access level',
		annotations: new ToolAnnotations(idempotentHint: true)
	)]
	public function updateLevel(
		#[Schema(description: 'The ID of the viewing access level to modify')]
		int $id,
		#[Schema(description: 'Title of the viewing access level', minLength: 1, maxLength: 255)]
		?string $title = null,
		#[Schema(description: 'An array of user group IDs that can access this level', items: ['type' => 'integer'], minItems: 1)]
		?array $rules = null
	)
	{
		$this->autologMCPTool();

		$postData = [
			'title' => $title,
			'rules' => $rules,
		];

		$writableFields = array_keys($postData);
		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/users/levels/' . $id);

		$postData = $this->prepareReadMergeUpdatePayload($http, (string) $uri, 'levels', $postData, $writableFields);

		$response = $http->patch($uri->toString(), json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'levels');
	}

	#[McpTool(
		name: 'users_levels_delete',
		description: 'Permanently delete a viewing access level',
		annotations: new ToolAnnotations(destructiveHint: true)
	)]
	public function deleteLevel(
		#[Schema(description: 'The ID of the viewing access level to delete')]
		int $id
	): bool
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/users/levels/' . $id);
		$response = $http->delete($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $response->getStatusCode() === 204;
	}
}

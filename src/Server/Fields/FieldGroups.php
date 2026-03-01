<?php
/**
 * @package       joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license       AGPL-3.0-or-later
 */

namespace Dionysopoulos\Mcp4Joomla\Server\Fields;

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
 * MCP elements for Joomla! custom field groups management
 */
class FieldGroups
{
	use HandleJoomlaAPIErrorTrait;
	use GetDataFromResponseTrait;
	use VarToLogTrait;
	use AutoLoggingTrait;
	use ReadMergeUpdateTrait;
	use JsonInputCompatibilityTrait;

	#[McpTool(
		name: 'fields_groups_list',
		description: 'List custom field groups for a given context',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function listFieldGroups(
		#[Schema(description: 'The field group context, e.g. "com_content.article"')]
		string $context,
		#[Schema(description: 'Search filter for field group titles')]
		?string $filterSearch = null,
		#[Schema(description: 'Filter by field group state: 0=unpublished, 1=published')]
		?int $filterState = null,
		#[Schema(description: 'Maximum number of items to return per page', minimum: 1)]
		?int $pageLimit = null,
		#[Schema(description: 'Starting record offset for pagination (0-based)', minimum: 0)]
		?int $pageOffset = null
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/fields/groups/' . $context);

		if ($filterSearch !== null)
		{
			$uri->setVar('filter[search]', $filterSearch);
		}

		if ($filterState !== null)
		{
			$uri->setVar('filter[state]', $filterState);
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

		return $this->getDataFromResponse($response, 'fieldgroups');
	}

	#[McpTool(
		name: 'fields_groups_read',
		description: 'Retrieve a specific custom field group',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function readFieldGroup(
		#[Schema(description: 'The field group context, e.g. "com_content.article"')]
		string $context,
		#[Schema(description: 'The ID of the field group to retrieve')]
		int $id
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/fields/groups/' . $context . '/' . $id);
		$response = $http->get($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'fieldgroups');
	}

	#[McpTool(
		name: 'fields_groups_create',
		description: 'Create a new custom field group'
	)]
	public function createFieldGroup(
		#[Schema(description: 'The field group context, e.g. "com_content.article"')]
		string $context,
		#[Schema(description: 'Field group title')]
		string $title,
		#[Schema(description: 'Field group description')]
		?string $description = null,
		#[Schema(description: 'Field group state: 0=unpublished, 1=published')]
		int $state = 1,
		#[Schema(description: 'Access level ID (default 1)')]
		?int $access = null,
		#[Schema(description: 'Language code or "*" for all languages')]
		string $language = '*',
		#[Schema(description: 'Optional note for the field group')]
		?string $note = null,
		#[Schema(description: 'Field group parameters as a JSON string or object')]
		array|string|null $params = null
	)
	{
		$this->autologMCPTool();

		$postData = [
			'title'       => $title,
			'description' => $description,
			'state'       => $state,
			'access'      => $access,
			'language'    => $language,
			'note'        => $note,
			'params'      => $this->normaliseJsonCompatibleInput($params),
		];

		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/fields/groups/' . $context);

		$response = $http->post($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'fieldgroups');
	}

	#[McpTool(
		name: 'fields_groups_update',
		description: 'Update an existing custom field group',
		annotations: new ToolAnnotations(idempotentHint: true)
	)]
	public function updateFieldGroup(
		#[Schema(description: 'The field group context, e.g. "com_content.article"')]
		string $context,
		#[Schema(description: 'The ID of the field group to update')]
		int $id,
		#[Schema(description: 'Field group title')]
		?string $title = null,
		#[Schema(description: 'Field group description')]
		?string $description = null,
		#[Schema(description: 'Field group state: 0=unpublished, 1=published')]
		?int $state = null,
		#[Schema(description: 'Access level ID (default 1)')]
		?int $access = null,
		#[Schema(description: 'Language code or "*" for all languages')]
		?string $language = null,
		#[Schema(description: 'Optional note for the field group')]
		?string $note = null,
		#[Schema(description: 'Field group parameters as a JSON string or object')]
		array|string|null $params = null
	)
	{
		$this->autologMCPTool();

		$postData = [
			'title'       => $title,
			'description' => $description,
			'state'       => $state,
			'access'      => $access,
			'language'    => $language,
			'note'        => $note,
			'params'      => $this->normaliseJsonCompatibleInput($params),
		];

		$writableFields = array_keys($postData);
		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/fields/groups/' . $context . '/' . $id);

		$postData = $this->prepareReadMergeUpdatePayload($http, (string) $uri, 'fieldgroups', $postData, $writableFields);

		$response = $http->patch($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'fieldgroups');
	}

	#[McpTool(
		name: 'fields_groups_delete',
		description: 'Delete a custom field group',
		annotations: new ToolAnnotations(destructiveHint: true)
	)]
	public function deleteFieldGroup(
		#[Schema(description: 'The field group context, e.g. "com_content.article"')]
		string $context,
		#[Schema(description: 'The ID of the field group to delete')]
		int $id
	): bool
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/fields/groups/' . $context . '/' . $id);
		$response = $http->delete($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $response->getStatusCode() === 204;
	}
}

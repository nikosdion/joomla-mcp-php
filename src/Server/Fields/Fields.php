<?php
/**
 * @package       joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license       AGPL-3.0-or-later
 */

declare(strict_types=1);

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
 * MCP elements for Joomla! custom fields management
 */
class Fields
{
	use HandleJoomlaAPIErrorTrait;
	use GetDataFromResponseTrait;
	use VarToLogTrait;
	use AutoLoggingTrait;
	use ReadMergeUpdateTrait;
	use JsonInputCompatibilityTrait;

	#[McpTool(
		name: 'fields_list',
		description: 'List custom fields for a given context',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function listFields(
		#[Schema(description: 'The field context, e.g. "com_content.article"')]
		string $context,
		#[Schema(description: 'Search filter for field titles')]
		?string $filterSearch = null,
		#[Schema(description: 'Filter by field state: 0=unpublished, 1=published')]
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
		$uri  = $http->getUri('v1/fields/' . $context);

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

		return $this->getDataFromResponse($response, 'fields');
	}

	#[McpTool(
		name: 'fields_read',
		description: 'Retrieve a specific custom field',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function readField(
		#[Schema(description: 'The field context, e.g. "com_content.article"')]
		string $context,
		#[Schema(description: 'The ID of the field to retrieve')]
		int $id
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/fields/' . $context . '/' . $id);
		$response = $http->get($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'fields');
	}

	#[McpTool(
		name: 'fields_create',
		description: 'Create a new custom field'
	)]
	public function createField(
		#[Schema(description: 'The field context, e.g. "com_content.article"')]
		string $context,
		#[Schema(description: 'Field title')]
		string $title,
		#[Schema(description: 'Field type: text, textarea, list, etc.')]
		string $type,
		#[Schema(description: 'Field name/alias')]
		?string $name = null,
		#[Schema(description: 'Field label')]
		?string $label = null,
		#[Schema(description: 'Field description')]
		?string $description = null,
		#[Schema(description: 'Field state: 0=unpublished, 1=published')]
		int $state = 1,
		#[Schema(description: 'Access level ID (default 1)')]
		?int $access = null,
		#[Schema(description: 'Field group ID')]
		?int $group_id = null,
		#[Schema(description: 'Language code or "*" for all languages')]
		string $language = '*',
		#[Schema(description: 'Default value for the field')]
		?string $default_value = null,
		#[Schema(description: 'Field parameters as a JSON string or object')]
		array|string|null $params = null,
		#[Schema(description: 'Field-specific parameters as a JSON string or object')]
		array|string|null $fieldparams = null
	)
	{
		$this->autologMCPTool();

		$postData = [
			'title'         => $title,
			'type'          => $type,
			'name'          => $name,
			'label'         => $label,
			'description'   => $description,
			'state'         => $state,
			'access'        => $access,
			'group_id'      => $group_id,
			'language'      => $language,
			'default_value' => $default_value,
			'params'        => $this->normaliseJsonCompatibleInput($params),
			'fieldparams'   => $this->normaliseJsonCompatibleInput($fieldparams),
		];

		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/fields/' . $context);

		$response = $http->post($uri->toString(), json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'fields');
	}

	#[McpTool(
		name: 'fields_update',
		description: 'Update an existing custom field',
		annotations: new ToolAnnotations(idempotentHint: true)
	)]
	public function updateField(
		#[Schema(description: 'The field context, e.g. "com_content.article"')]
		string $context,
		#[Schema(description: 'The ID of the field to update')]
		int $id,
		#[Schema(description: 'Field title')]
		?string $title = null,
		#[Schema(description: 'Field type: text, textarea, list, etc.')]
		?string $type = null,
		#[Schema(description: 'Field name/alias')]
		?string $name = null,
		#[Schema(description: 'Field label')]
		?string $label = null,
		#[Schema(description: 'Field description')]
		?string $description = null,
		#[Schema(description: 'Field state: 0=unpublished, 1=published')]
		?int $state = null,
		#[Schema(description: 'Access level ID (default 1)')]
		?int $access = null,
		#[Schema(description: 'Field group ID')]
		?int $group_id = null,
		#[Schema(description: 'Language code or "*" for all languages')]
		?string $language = null,
		#[Schema(description: 'Default value for the field')]
		?string $default_value = null,
		#[Schema(description: 'Field parameters as a JSON string or object')]
		array|string|null $params = null,
		#[Schema(description: 'Field-specific parameters as a JSON string or object')]
		array|string|null $fieldparams = null
	)
	{
		$this->autologMCPTool();

		$postData = [
			'title'         => $title,
			'type'          => $type,
			'name'          => $name,
			'label'         => $label,
			'description'   => $description,
			'state'         => $state,
			'access'        => $access,
			'group_id'      => $group_id,
			'language'      => $language,
			'default_value' => $default_value,
			'params'        => $this->normaliseJsonCompatibleInput($params),
			'fieldparams'   => $this->normaliseJsonCompatibleInput($fieldparams),
		];

		$writableFields = array_keys($postData);
		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/fields/' . $context . '/' . $id);

		$postData = $this->prepareReadMergeUpdatePayload($http, (string) $uri, 'fields', $postData, $writableFields);

		$response = $http->patch($uri->toString(), json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'fields');
	}

	#[McpTool(
		name: 'fields_delete',
		description: 'Delete a custom field',
		annotations: new ToolAnnotations(destructiveHint: true)
	)]
	public function deleteField(
		#[Schema(description: 'The field context, e.g. "com_content.article"')]
		string $context,
		#[Schema(description: 'The ID of the field to delete')]
		int $id
	): bool
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/fields/' . $context . '/' . $id);
		$response = $http->delete($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $response->getStatusCode() === 204;
	}
}

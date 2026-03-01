<?php
/**
 * @package       joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license       AGPL-3.0-or-later
 */

namespace Dionysopoulos\Mcp4Joomla\Server\Modules;

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
 * MCP elements for Joomla! administrator modules management
 */
class AdminModules
{
	use HandleJoomlaAPIErrorTrait;
	use GetDataFromResponseTrait;
	use VarToLogTrait;
	use AutoLoggingTrait;
	use ReadMergeUpdateTrait;
	use JsonInputCompatibilityTrait;

	#[McpTool(
		name: 'modules_admin_list',
		description: 'List existing administrator modules',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function listModules(
		#[Schema(description: 'Search filter for the module title')]
		?string $filterSearch = null,
		#[Schema(
			description: 'Filter by publish state: 0=unpublished, 1=published, 2=archived, -2=trashed',
			enum: [null, 0, 1, 2, -2]
		)]
		?int $filterState = null,
		#[Schema(description: 'Filter by module position')]
		?int $filterPosition = null,
		#[Schema(description: 'Filter by module type (e.g. mod_menu)')]
		?string $filterModule = null,
		#[Schema(description: 'Filter by access level')]
		?int $filterAccess = null
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/modules/administrator');

		if ($filterSearch !== null)
		{
			$uri->setVar('filter[search]', $filterSearch);
		}

		if ($filterState !== null)
		{
			$uri->setVar('filter[state]', $filterState);
		}

		if ($filterPosition !== null)
		{
			$uri->setVar('filter[position]', $filterPosition);
		}

		if ($filterModule !== null)
		{
			$uri->setVar('filter[module]', $filterModule);
		}

		if ($filterAccess !== null)
		{
			$uri->setVar('filter[access]', $filterAccess);
		}

		$response = $http->get($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'modules');
	}

	#[McpTool(
		name: 'modules_admin_read',
		description: 'Retrieve the information of the specified administrator module',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function readModule(
		#[Schema(description: 'The ID of the module to retrieve')]
		int $id
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/modules/administrator/' . $id);
		$response = $http->get($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'modules');
	}

	#[McpTool(
		name: 'modules_admin_create',
		description: 'Create a new administrator module'
	)]
	public function createModule(
		#[Schema(description: 'Module title', minLength: 1, maxLength: 255)]
		string $title,
		#[Schema(description: 'Module type (e.g. mod_menu)', minLength: 1)]
		string $module,
		#[Schema(
			description: 'Publish state: 0=unpublished, 1=published',
			enum: [0, 1]
		)]
		int $published = 1,
		#[Schema(description: 'The Joomla! viewing access level for the module')]
		?int $access = null,
		#[Schema(description: 'Module position')]
		?int $position = null,
		#[Schema(description: 'Optional note for the module')]
		?string $note = null,
		#[Schema(description: 'Module ordering')]
		?int $ordering = null,
		#[Schema(description: 'Language code for the module, or "*" for all languages', pattern: '^(\*|[a-z]{2}(-[A-Z]{2})?)$')]
		string $language = '*',
		#[Schema(description: 'Menu assignment')]
		?string $assignment = null,
		#[Schema(description: 'Module parameters as a JSON string or object')]
		array|string|null $params = null
	)
	{
		$this->autologMCPTool();

		$postData = [
			'title'      => $title,
			'module'     => $module,
			'published'  => $published,
			'access'     => $access ?? 1,
			'position'   => $position,
			'note'       => $note,
			'ordering'   => $ordering,
			'language'   => $language,
			'assignment' => $assignment,
			'params'     => $this->normaliseJsonCompatibleInput($params),
		];

		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/modules/administrator');

		$response = $http->post($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'modules');
	}

	#[McpTool(
		name: 'modules_admin_update',
		description: 'Update an existing administrator module',
		annotations: new ToolAnnotations(idempotentHint: true)
	)]
	public function updateModule(
		#[Schema(description: 'The ID of the module to modify')]
		int $id,
		#[Schema(description: 'Module title', minLength: 1, maxLength: 255)]
		?string $title = null,
		#[Schema(description: 'Module type (e.g. mod_menu)', minLength: 1)]
		?string $module = null,
		#[Schema(
			description: 'Publish state: null=no change, 0=unpublished, 1=published, -2=trashed',
			enum: [null, 0, 1, -2]
		)]
		?int $published = null,
		#[Schema(description: 'The Joomla! viewing access level for the module')]
		?int $access = null,
		#[Schema(description: 'Module position')]
		?int $position = null,
		#[Schema(description: 'Optional note for the module')]
		?string $note = null,
		#[Schema(description: 'Module ordering')]
		?int $ordering = null,
		#[Schema(description: 'Language code for the module, or "*" for all languages', pattern: '^(\*|[a-z]{2}(-[A-Z]{2})?)$')]
		?string $language = null,
		#[Schema(description: 'Menu assignment')]
		?string $assignment = null,
		#[Schema(description: 'Module parameters as a JSON string or object')]
		array|string|null $params = null
	)
	{
		$this->autologMCPTool();

		$postData = [
			'title'      => $title,
			'module'     => $module,
			'published'  => $published,
			'access'     => $access,
			'position'   => $position,
			'note'       => $note,
			'ordering'   => $ordering,
			'language'   => $language,
			'assignment' => $assignment,
			'params'     => $this->normaliseJsonCompatibleInput($params),
		];

		$writableFields = array_keys($postData);
		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/modules/administrator/' . $id);

		$postData = $this->prepareReadMergeUpdatePayload($http, (string) $uri, 'modules', $postData, $writableFields);

		$response = $http->patch($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'modules');
	}

	#[McpTool(
		name: 'modules_admin_trash',
		description: 'Moves an administrator module to the trash by setting its state to -2',
		annotations: new ToolAnnotations(destructiveHint: true)
	)]
	public function trashModule(
		#[Schema(description: 'The ID of the module to trash')]
		int $id
	)
	{
		$this->autologMCPTool();

		return $this->updateModule(id: $id, published: -2);
	}

	#[McpTool(
		name: 'modules_admin_delete',
		description: 'Permanently deletes an administrator module. Automatically trashes it first if needed.',
		annotations: new ToolAnnotations(destructiveHint: true)
	)]
	public function deleteModule(
		#[Schema(description: 'The ID of the module to delete')]
		int $id
	): bool
	{
		$this->autologMCPTool();

		$this->trashModule($id);

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/modules/administrator/' . $id);
		$response = $http->delete($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $response->getStatusCode() === 204;
	}

	#[McpTool(
		name: 'modules_admin_types',
		description: 'List available administrator module types',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function listModuleTypes()
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/modules/types/administrator');
		$response = $http->get($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, null);
	}
}

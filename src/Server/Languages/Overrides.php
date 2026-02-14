<?php
/**
 * @package       joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license       AGPL-3.0-or-later
 */

namespace Dionysopoulos\Mcp4Joomla\Server\Languages;

use Dionysopoulos\Mcp4Joomla\Container\Factory;
use Dionysopoulos\Mcp4Joomla\Utility\AutoLoggingTrait;
use Dionysopoulos\Mcp4Joomla\Utility\GetDataFromResponseTrait;
use Dionysopoulos\Mcp4Joomla\Utility\HandleJoomlaAPIErrorTrait;
use Dionysopoulos\Mcp4Joomla\Utility\HttpDecorator;
use Dionysopoulos\Mcp4Joomla\Utility\VarToLogTrait;
use PhpMcp\Schema\ToolAnnotations;
use PhpMcp\Server\Attributes\McpTool;
use PhpMcp\Server\Attributes\Schema;

/**
 * MCP elements for Joomla! language override management
 */
class Overrides
{
	use HandleJoomlaAPIErrorTrait;
	use GetDataFromResponseTrait;
	use VarToLogTrait;
	use AutoLoggingTrait;

	// =========================================================================
	// Site language overrides
	// =========================================================================

	#[McpTool(
		name: 'languages_overrides_site_list',
		description: 'List site language overrides for a given language',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function listSiteOverrides(
		#[Schema(description: 'The language code, e.g. "en-GB"')]
		string $language,
		#[Schema(description: 'Search overrides by key or value')]
		?string $filterSearch = null
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/languages/overrides/site/' . $language);

		if ($filterSearch !== null)
		{
			$uri->setVar('filter[search]', $filterSearch);
		}

		$response = $http->get($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'languages');
	}

	#[McpTool(
		name: 'languages_overrides_site_read',
		description: 'Retrieve a specific site language override',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function readSiteOverride(
		#[Schema(description: 'The language code, e.g. "en-GB"')]
		string $language,
		#[Schema(description: 'The language constant key')]
		string $key
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/languages/overrides/site/' . $language . '/' . $key);
		$response = $http->get($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'languages');
	}

	#[McpTool(
		name: 'languages_overrides_site_create',
		description: 'Create a new site language override'
	)]
	public function createSiteOverride(
		#[Schema(description: 'The language code, e.g. "en-GB"')]
		string $language,
		#[Schema(description: 'The language constant key')]
		string $key,
		#[Schema(description: 'The override value')]
		string $value
	)
	{
		$this->autologMCPTool();

		$postData = [
			'key'      => $key,
			'override' => $value,
		];

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/languages/overrides/site/' . $language);

		$response = $http->post($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'languages');
	}

	#[McpTool(
		name: 'languages_overrides_site_update',
		description: 'Update an existing site language override',
		annotations: new ToolAnnotations(idempotentHint: true)
	)]
	public function updateSiteOverride(
		#[Schema(description: 'The language code, e.g. "en-GB"')]
		string $language,
		#[Schema(description: 'The language constant key')]
		string $key,
		#[Schema(description: 'The new override value')]
		string $value
	)
	{
		$this->autologMCPTool();

		$postData = ['override' => $value];

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/languages/overrides/site/' . $language . '/' . $key);

		$response = $http->patch($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'languages');
	}

	#[McpTool(
		name: 'languages_overrides_site_delete',
		description: 'Delete a site language override',
		annotations: new ToolAnnotations(destructiveHint: true)
	)]
	public function deleteSiteOverride(
		#[Schema(description: 'The language code, e.g. "en-GB"')]
		string $language,
		#[Schema(description: 'The language constant key')]
		string $key
	): bool
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/languages/overrides/site/' . $language . '/' . $key);
		$response = $http->delete($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $response->getStatusCode() === 204;
	}

	// =========================================================================
	// Administrator language overrides
	// =========================================================================

	#[McpTool(
		name: 'languages_overrides_admin_list',
		description: 'List administrator language overrides for a given language',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function listAdminOverrides(
		#[Schema(description: 'The language code, e.g. "en-GB"')]
		string $language,
		#[Schema(description: 'Search overrides by key or value')]
		?string $filterSearch = null
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/languages/overrides/administrator/' . $language);

		if ($filterSearch !== null)
		{
			$uri->setVar('filter[search]', $filterSearch);
		}

		$response = $http->get($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'languages');
	}

	#[McpTool(
		name: 'languages_overrides_admin_read',
		description: 'Retrieve a specific administrator language override',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function readAdminOverride(
		#[Schema(description: 'The language code, e.g. "en-GB"')]
		string $language,
		#[Schema(description: 'The language constant key')]
		string $key
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/languages/overrides/administrator/' . $language . '/' . $key);
		$response = $http->get($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'languages');
	}

	#[McpTool(
		name: 'languages_overrides_admin_create',
		description: 'Create a new administrator language override'
	)]
	public function createAdminOverride(
		#[Schema(description: 'The language code, e.g. "en-GB"')]
		string $language,
		#[Schema(description: 'The language constant key')]
		string $key,
		#[Schema(description: 'The override value')]
		string $value
	)
	{
		$this->autologMCPTool();

		$postData = [
			'key'      => $key,
			'override' => $value,
		];

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/languages/overrides/administrator/' . $language);

		$response = $http->post($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'languages');
	}

	#[McpTool(
		name: 'languages_overrides_admin_update',
		description: 'Update an existing administrator language override',
		annotations: new ToolAnnotations(idempotentHint: true)
	)]
	public function updateAdminOverride(
		#[Schema(description: 'The language code, e.g. "en-GB"')]
		string $language,
		#[Schema(description: 'The language constant key')]
		string $key,
		#[Schema(description: 'The new override value')]
		string $value
	)
	{
		$this->autologMCPTool();

		$postData = ['override' => $value];

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/languages/overrides/administrator/' . $language . '/' . $key);

		$response = $http->patch($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'languages');
	}

	#[McpTool(
		name: 'languages_overrides_admin_delete',
		description: 'Delete an administrator language override',
		annotations: new ToolAnnotations(destructiveHint: true)
	)]
	public function deleteAdminOverride(
		#[Schema(description: 'The language code, e.g. "en-GB"')]
		string $language,
		#[Schema(description: 'The language constant key')]
		string $key
	): bool
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/languages/overrides/administrator/' . $language . '/' . $key);
		$response = $http->delete($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $response->getStatusCode() === 204;
	}

	// =========================================================================
	// Language override search
	// =========================================================================

	#[McpTool(
		name: 'languages_overrides_search',
		description: 'Search language strings for creating overrides',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function searchOverrides(
		#[Schema(description: 'The language code, e.g. "en-GB"')]
		string $language,
		#[Schema(description: 'The string to search for')]
		string $searchString,
		#[Schema(description: 'The type of search: "constant" or "value"')]
		?string $searchType = null
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/languages/overrides/search');

		$uri->setVar('filter[language]', $language);
		$uri->setVar('filter[searchstring]', $searchString);

		if ($searchType !== null)
		{
			$uri->setVar('filter[searchtype]', $searchType);
		}

		$response = $http->get($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'languages');
	}

	#[McpTool(
		name: 'languages_overrides_cache_refresh',
		description: 'Refresh the language override search cache'
	)]
	public function refreshCache()
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/languages/overrides/search/cache/refresh');

		$response = $http->post($uri, '', ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'languages');
	}
}

<?php
/**
 * @package       joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license       AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Dionysopoulos\Mcp4Joomla\Server\Panopticon;

use Dionysopoulos\Mcp4Joomla\Container\Factory;
use Dionysopoulos\Mcp4Joomla\Utility\AutoLoggingTrait;
use Dionysopoulos\Mcp4Joomla\Utility\GetDataFromResponseTrait;
use Dionysopoulos\Mcp4Joomla\Utility\HandleJoomlaAPIErrorTrait;
use Dionysopoulos\Mcp4Joomla\Utility\HttpDecorator;
use Dionysopoulos\Mcp4Joomla\Utility\VarToLogTrait;
use PhpMcp\Schema\ToolAnnotations;
use PhpMcp\Server\Attributes\McpTool;
use PhpMcp\Server\Attributes\Schema;

class UpdateSites
{
	use HandleJoomlaAPIErrorTrait;
	use GetDataFromResponseTrait;
	use VarToLogTrait;
	use AutoLoggingTrait;

	#[McpTool(
		name: 'panopticon_updatesites_list',
		description: 'List update sites',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function listUpdateSites(
		#[Schema(description: 'Search filter for update sites')]
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
		$uri  = $http->getUri('v1/panopticon/updatesites');

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

		return $this->getDataFromResponse($response, 'updatesites');
	}

	#[McpTool(
		name: 'panopticon_updatesites_read',
		description: 'Read details of a specific update site',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function readUpdateSite(
		#[Schema(description: 'The ID of the update site to retrieve')]
		int $id
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/panopticon/updatesites/' . $id);
		$response = $http->get($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'updatesites');
	}

	#[McpTool(
		name: 'panopticon_updatesites_update',
		description: 'Update an existing update site',
		annotations: new ToolAnnotations(idempotentHint: true)
	)]
	public function updateUpdateSite(
		#[Schema(description: 'The ID of the update site to update')]
		int $id,
		#[Schema(description: 'Enable or disable the update site', enum: [0, 1])]
		?int $enabled = null,
		#[Schema(description: 'The name of the update site')]
		?string $name = null,
		#[Schema(description: 'The URL location of the update site')]
		?string $location = null,
		#[Schema(description: 'Extra query string to append to the update site URL')]
		?string $extraQuery = null
	)
	{
		$this->autologMCPTool();

		$postData = [
			'enabled'     => $enabled,
			'name'        => $name,
			'location'    => $location,
			'extra_query' => $extraQuery,
		];

		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/panopticon/updatesites/' . $id);

		$response = $http->patch($uri->toString(), json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'updatesites');
	}

	#[McpTool(
		name: 'panopticon_updatesites_delete',
		description: 'Delete an update site',
		annotations: new ToolAnnotations(destructiveHint: true)
	)]
	public function deleteUpdateSite(
		#[Schema(description: 'The ID of the update site to delete')]
		int $id
	): bool
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/panopticon/updatesites/' . $id);
		$response = $http->delete($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $response->getStatusCode() === 204;
	}

	#[McpTool(
		name: 'panopticon_updatesites_rebuild',
		description: 'Rebuild the update sites table'
	)]
	public function rebuildUpdateSites()
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/panopticon/updatesites/rebuild');

		$response = $http->post($uri->toString(), '', ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, null);
	}
}

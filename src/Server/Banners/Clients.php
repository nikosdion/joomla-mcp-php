<?php
/**
 * @package       joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license       AGPL-3.0-or-later
 */

namespace Dionysopoulos\Mcp4Joomla\Server\Banners;

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
 * MCP elements for Joomla! banner clients management
 */
class Clients
{
	use HandleJoomlaAPIErrorTrait;
	use GetDataFromResponseTrait;
	use VarToLogTrait;
	use AutoLoggingTrait;

	#[McpTool(
		name: 'banners_clients_list',
		description: 'List existing banner clients',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function listClients(
		#[Schema(description: 'Search filter for the client name')]
		?string $filterSearch = null,
		#[Schema(
			description: 'Filter by state: 0=unpublished, 1=published, 2=archived, -2=trashed',
			enum: [null, 0, 1, 2, -2]
		)]
		?int $filterState = null
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/banners/clients');

		if ($filterSearch !== null)
		{
			$uri->setVar('filter[search]', $filterSearch);
		}

		if ($filterState !== null)
		{
			$uri->setVar('filter[state]', $filterState);
		}

		$response = $http->get($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'clients');
	}

	#[McpTool(
		name: 'banners_clients_read',
		description: 'Retrieve the information of the specified banner client',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function readClient(
		#[Schema(description: 'The ID of the client to retrieve')]
		int $id
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/banners/clients/' . $id);
		$response = $http->get($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'clients');
	}

	#[McpTool(
		name: 'banners_clients_create',
		description: 'Create a new banner client'
	)]
	public function createClient(
		#[Schema(description: 'Client contact name', minLength: 1, maxLength: 255)]
		string $name,
		#[Schema(description: 'Client company/display name', minLength: 1, maxLength: 255)]
		string $contactName,
		#[Schema(description: 'Client email address', format: 'email')]
		?string $email = null,
		#[Schema(description: 'Extra information about the client')]
		?string $extraInfo = null,
		#[Schema(
			description: 'Publish state: 0=unpublished, 1=published',
			enum: [0, 1]
		)]
		int $state = 1,
		#[Schema(description: 'Purchase type')]
		?int $purchaseType = null,
		#[Schema(description: 'Track impressions')]
		?int $trackImpressions = null,
		#[Schema(description: 'Track clicks')]
		?int $trackClicks = null,
		#[Schema(description: 'Optional meta keywords, separated by commas')]
		?string $metakey = null,
		#[Schema(description: 'Optional meta description')]
		?string $metadesc = null
	)
	{
		$this->autologMCPTool();

		$postData = [
			'contact'           => $name,
			'name'              => $contactName,
			'email'             => $email,
			'extrainfo'         => $extraInfo,
			'state'             => $state,
			'purchase_type'     => $purchaseType,
			'track_impressions' => $trackImpressions,
			'track_clicks'      => $trackClicks,
			'metakey'           => $metakey,
			'metadesc'          => $metadesc,
		];

		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/banners/clients');

		$response = $http->post($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'clients');
	}

	#[McpTool(
		name: 'banners_clients_update',
		description: 'Update an existing banner client',
		annotations: new ToolAnnotations(idempotentHint: true)
	)]
	public function updateClient(
		#[Schema(description: 'The ID of the client to modify')]
		int $id,
		#[Schema(description: 'Client contact name', minLength: 1, maxLength: 255)]
		?string $name = null,
		#[Schema(description: 'Client company/display name', minLength: 1, maxLength: 255)]
		?string $contactName = null,
		#[Schema(description: 'Client email address', format: 'email')]
		?string $email = null,
		#[Schema(description: 'Extra information about the client')]
		?string $extraInfo = null,
		#[Schema(
			description: 'Publish state: null=no change, 0=unpublished, 1=published',
			enum: [null, 0, 1]
		)]
		?int $state = null,
		#[Schema(description: 'Purchase type')]
		?int $purchaseType = null,
		#[Schema(description: 'Track impressions')]
		?int $trackImpressions = null,
		#[Schema(description: 'Track clicks')]
		?int $trackClicks = null,
		#[Schema(description: 'Optional meta keywords, separated by commas')]
		?string $metakey = null,
		#[Schema(description: 'Optional meta description')]
		?string $metadesc = null
	)
	{
		$this->autologMCPTool();

		$postData = [
			'contact'           => $name,
			'name'              => $contactName,
			'email'             => $email,
			'extrainfo'         => $extraInfo,
			'state'             => $state,
			'purchase_type'     => $purchaseType,
			'track_impressions' => $trackImpressions,
			'track_clicks'      => $trackClicks,
			'metakey'           => $metakey,
			'metadesc'          => $metadesc,
		];

		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/banners/clients/' . $id);

		$response = $http->patch($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'clients');
	}

	#[McpTool(
		name: 'banners_clients_delete',
		description: 'Permanently deletes a banner client. The client MUST be set to a trashed state (-2) before calling this method.',
		annotations: new ToolAnnotations(destructiveHint: true)
	)]
	public function deleteClient(
		#[Schema(description: 'The ID of the client to delete')]
		int $id
	): bool
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/banners/clients/' . $id);
		$response = $http->delete($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $response->getStatusCode() === 204;
	}
}

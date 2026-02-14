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
use Dionysopoulos\Mcp4Joomla\Utility\TitleToAliasTrait;
use Dionysopoulos\Mcp4Joomla\Utility\VarToLogTrait;
use PhpMcp\Schema\ToolAnnotations;
use PhpMcp\Server\Attributes\McpTool;
use PhpMcp\Server\Attributes\Schema;

/**
 * MCP elements for Joomla! banners management
 */
class Banners
{
	use HandleJoomlaAPIErrorTrait;
	use GetDataFromResponseTrait;
	use VarToLogTrait;
	use AutoLoggingTrait;
	use TitleToAliasTrait;

	#[McpTool(
		name: 'banners_list',
		description: 'List existing banners',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function listBanners(
		#[Schema(description: 'Search filter for the banner name')]
		?string $filterSearch = null,
		#[Schema(
			description: 'Filter by publish state: 0=unpublished, 1=published, 2=archived, -2=trashed',
			enum: [null, 0, 1, 2, -2]
		)]
		?int $filterState = null,
		#[Schema(description: 'Filter by category ID')]
		?int $filterCategory = null,
		#[Schema(description: 'Filter by client ID')]
		?int $filterClient = null
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/banners');

		if ($filterSearch !== null)
		{
			$uri->setVar('filter[search]', $filterSearch);
		}

		if ($filterState !== null)
		{
			$uri->setVar('filter[state]', $filterState);
		}

		if ($filterCategory !== null)
		{
			$uri->setVar('filter[category]', $filterCategory);
		}

		if ($filterClient !== null)
		{
			$uri->setVar('filter[client_id]', $filterClient);
		}

		$response = $http->get($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'banners');
	}

	#[McpTool(
		name: 'banners_read',
		description: 'Retrieve the information of the specified banner',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function readBanner(
		#[Schema(description: 'The ID of the banner to retrieve')]
		int $id
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/banners/' . $id);
		$response = $http->get($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'banners');
	}

	#[McpTool(
		name: 'banners_create',
		description: 'Create a new banner'
	)]
	public function createBanner(
		#[Schema(description: 'Banner name', minLength: 1, maxLength: 255)]
		string $name,
		#[Schema(description: 'URL slug for the banner', pattern: '^[a-z0-9-_]+$')]
		?string $alias = null,
		#[Schema(description: 'Banner category ID', minimum: 0, exclusiveMinimum: true)]
		int $catId,
		#[Schema(
			description: 'Publish state: 0=unpublished, 1=published, 2=archived, -2=trashed',
			enum: [0, 1, 2, -2]
		)]
		int $state = 1,
		#[Schema(description: 'Banner client ID')]
		?int $cid = null,
		#[Schema(description: 'Banner type: 0=image, 1=custom', enum: ['0', '1'])]
		?string $type = null,
		#[Schema(description: 'Click URL for the banner')]
		?string $clickUrl = null,
		#[Schema(description: 'Custom banner HTML code')]
		?string $customBannerCode = null,
		#[Schema(description: 'Purchase type')]
		?int $purchaseType = null,
		#[Schema(description: 'Track impressions')]
		?int $trackImpressions = null,
		#[Schema(description: 'Track clicks')]
		?int $trackClicks = null,
		#[Schema(description: 'An array of tag IDs to associate with the banner', items: ['type' => 'integer'], minItems: 0, uniqueItems: true)]
		?array $tags = null,
		#[Schema(description: 'Banner description')]
		?string $description = null,
		#[Schema(description: 'Language code for the banner, or "*" for all languages', pattern: '^(\*|[a-z]{2}(-[A-Z]{2})?)$')]
		string $language = '*',
		#[Schema(description: 'Optional meta keywords, separated by commas')]
		?string $metakey = null,
		#[Schema(description: 'Optional meta description')]
		?string $metadesc = null
	)
	{
		$this->autologMCPTool();

		$postData = [
			'name'             => $name,
			'alias'            => $alias ?: $this->titleToAlias($name),
			'catid'            => $catId,
			'state'            => $state,
			'cid'              => $cid,
			'type'             => $type,
			'clickurl'         => $clickUrl,
			'custombannercode' => $customBannerCode,
			'purchase_type'    => $purchaseType,
			'track_impressions' => $trackImpressions,
			'track_clicks'     => $trackClicks,
			'tags'             => $tags,
			'description'      => $description,
			'language'         => $language,
			'metakey'          => $metakey,
			'metadesc'         => $metadesc,
		];

		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/banners');

		$response = $http->post($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'banners');
	}

	#[McpTool(
		name: 'banners_update',
		description: 'Update an existing banner',
		annotations: new ToolAnnotations(idempotentHint: true)
	)]
	public function updateBanner(
		#[Schema(description: 'The ID of the banner to modify')]
		int $id,
		#[Schema(description: 'Banner name', minLength: 1, maxLength: 255)]
		?string $name = null,
		#[Schema(description: 'URL slug for the banner', pattern: '^[a-z0-9-_]+$')]
		?string $alias = null,
		#[Schema(description: 'Banner category ID', minimum: 0, exclusiveMinimum: true)]
		?int $catId = null,
		#[Schema(
			description: 'Publish state: null=no change, 0=unpublished, 1=published, 2=archived, -2=trashed',
			enum: [null, 0, 1, 2, -2]
		)]
		?int $state = null,
		#[Schema(description: 'Banner client ID')]
		?int $cid = null,
		#[Schema(description: 'Banner type: 0=image, 1=custom', enum: ['0', '1'])]
		?string $type = null,
		#[Schema(description: 'Click URL for the banner')]
		?string $clickUrl = null,
		#[Schema(description: 'Custom banner HTML code')]
		?string $customBannerCode = null,
		#[Schema(description: 'Purchase type')]
		?int $purchaseType = null,
		#[Schema(description: 'Track impressions')]
		?int $trackImpressions = null,
		#[Schema(description: 'Track clicks')]
		?int $trackClicks = null,
		#[Schema(description: 'An array of tag IDs to associate with the banner', items: ['type' => 'integer'], minItems: 0, uniqueItems: true)]
		?array $tags = null,
		#[Schema(description: 'Banner description')]
		?string $description = null,
		#[Schema(description: 'Language code for the banner, or "*" for all languages', pattern: '^(\*|[a-z]{2}(-[A-Z]{2})?)$')]
		?string $language = null,
		#[Schema(description: 'Optional meta keywords, separated by commas')]
		?string $metakey = null,
		#[Schema(description: 'Optional meta description')]
		?string $metadesc = null
	)
	{
		$this->autologMCPTool();

		$postData = [
			'name'             => $name,
			'alias'            => $alias,
			'catid'            => $catId,
			'state'            => $state,
			'cid'              => $cid,
			'type'             => $type,
			'clickurl'         => $clickUrl,
			'custombannercode' => $customBannerCode,
			'purchase_type'    => $purchaseType,
			'track_impressions' => $trackImpressions,
			'track_clicks'     => $trackClicks,
			'tags'             => $tags,
			'description'      => $description,
			'language'         => $language,
			'metakey'          => $metakey,
			'metadesc'         => $metadesc,
		];

		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/banners/' . $id);

		$response = $http->patch($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'banners');
	}

	#[McpTool(
		name: 'banners_delete',
		description: 'Permanently deletes a banner. The banner MUST be set to a trashed state (-2) before calling this method.',
		annotations: new ToolAnnotations(destructiveHint: true)
	)]
	public function deleteBanner(
		#[Schema(description: 'The ID of the banner to delete')]
		int $id
	): bool
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/banners/' . $id);
		$response = $http->delete($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $response->getStatusCode() === 204;
	}
}

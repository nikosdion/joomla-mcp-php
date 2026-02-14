<?php
/**
 * @package       joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license       AGPL-3.0-or-later
 */

namespace Dionysopoulos\Mcp4Joomla\Server\Newsfeeds;

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
 * MCP elements for Joomla! newsfeeds management
 */
class Feeds
{
	use HandleJoomlaAPIErrorTrait;
	use GetDataFromResponseTrait;
	use VarToLogTrait;
	use AutoLoggingTrait;
	use TitleToAliasTrait;

	#[McpTool(
		name: 'newsfeeds_list',
		description: 'List existing newsfeeds',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function listFeeds(
		#[Schema(description: 'Search filter for the newsfeed name')]
		?string $filterSearch = null,
		#[Schema(
			description: 'Filter by publish state: 0=unpublished, 1=published, 2=archived, -2=trashed',
			enum: [null, 0, 1, 2, -2]
		)]
		?int $filterState = null,
		#[Schema(description: 'Filter by category ID')]
		?int $filterCategory = null,
		#[Schema(description: 'Filter by language code, or "*" for all languages', pattern: '^(\*|[a-z]{2}(-[A-Z]{2})?)$')]
		?string $filterLanguage = null
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/newsfeeds/feeds');

		if ($filterSearch !== null)
		{
			$uri->setVar('filter[search]', $filterSearch);
		}

		if ($filterState !== null)
		{
			$uri->setVar('filter[published]', $filterState);
		}

		if ($filterCategory !== null)
		{
			$uri->setVar('filter[category]', $filterCategory);
		}

		if ($filterLanguage !== null)
		{
			$uri->setVar('filter[language]', $filterLanguage);
		}

		$response = $http->get($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'newsfeeds');
	}

	#[McpTool(
		name: 'newsfeeds_read',
		description: 'Retrieve the information of the specified newsfeed',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function readFeed(
		#[Schema(description: 'The ID of the newsfeed to retrieve')]
		int $id
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/newsfeeds/feeds/' . $id);
		$response = $http->get($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'newsfeeds');
	}

	#[McpTool(
		name: 'newsfeeds_create',
		description: 'Create a new newsfeed'
	)]
	public function createFeed(
		#[Schema(description: 'Newsfeed name', minLength: 1, maxLength: 255)]
		string $name,
		#[Schema(description: 'Feed URL', format: 'uri')]
		string $link,
		#[Schema(description: 'Newsfeed category ID', minimum: 0, exclusiveMinimum: true)]
		int $catId,
		#[Schema(description: 'URL slug for the newsfeed', pattern: '^[a-z0-9-_]+$')]
		?string $alias = null,
		#[Schema(
			description: 'Publish state: 0=unpublished, 1=published, 2=archived, -2=trashed',
			enum: [0, 1, 2, -2]
		)]
		int $published = 1,
		#[Schema(description: 'The Joomla! viewing access level for the newsfeed')]
		?int $access = null,
		#[Schema(description: 'Language code for the newsfeed, or "*" for all languages', pattern: '^(\*|[a-z]{2}(-[A-Z]{2})?)$')]
		string $language = '*',
		#[Schema(description: 'Newsfeed description')]
		?string $description = null,
		#[Schema(description: 'Number of articles to display from the feed', minimum: 0)]
		?int $numArticles = null,
		#[Schema(description: 'Cache time in seconds', minimum: 0)]
		?int $cacheTime = null,
		#[Schema(description: 'Newsfeed ordering')]
		?int $ordering = null,
		#[Schema(description: 'An array of tag IDs to associate with the newsfeed', items: ['type' => 'integer'], minItems: 0, uniqueItems: true)]
		?array $tags = null,
		#[Schema(description: 'Optional meta description')]
		?string $metadesc = null,
		#[Schema(description: 'Optional meta keywords, separated by commas')]
		?string $metakey = null
	)
	{
		$this->autologMCPTool();

		$postData = [
			'name'        => $name,
			'link'        => $link,
			'catid'       => $catId,
			'alias'       => $alias ?: $this->titleToAlias($name),
			'published'   => $published,
			'access'      => $access ?? 1,
			'language'    => $language,
			'description' => $description,
			'numarticles' => $numArticles,
			'cache_time'  => $cacheTime,
			'ordering'    => $ordering,
			'tags'        => $tags,
			'metadesc'    => $metadesc,
			'metakey'     => $metakey,
		];

		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/newsfeeds/feeds');

		$response = $http->post($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'newsfeeds');
	}

	#[McpTool(
		name: 'newsfeeds_update',
		description: 'Update an existing newsfeed',
		annotations: new ToolAnnotations(idempotentHint: true)
	)]
	public function updateFeed(
		#[Schema(description: 'The ID of the newsfeed to modify')]
		int $id,
		#[Schema(description: 'Newsfeed name', minLength: 1, maxLength: 255)]
		?string $name = null,
		#[Schema(description: 'Feed URL', format: 'uri')]
		?string $link = null,
		#[Schema(description: 'Newsfeed category ID', minimum: 0, exclusiveMinimum: true)]
		?int $catId = null,
		#[Schema(description: 'URL slug for the newsfeed', pattern: '^[a-z0-9-_]+$')]
		?string $alias = null,
		#[Schema(
			description: 'Publish state: null=no change, 0=unpublished, 1=published, 2=archived, -2=trashed',
			enum: [null, 0, 1, 2, -2]
		)]
		?int $published = null,
		#[Schema(description: 'The Joomla! viewing access level for the newsfeed')]
		?int $access = null,
		#[Schema(description: 'Language code for the newsfeed, or "*" for all languages', pattern: '^(\*|[a-z]{2}(-[A-Z]{2})?)$')]
		?string $language = null,
		#[Schema(description: 'Newsfeed description')]
		?string $description = null,
		#[Schema(description: 'Number of articles to display from the feed', minimum: 0)]
		?int $numArticles = null,
		#[Schema(description: 'Cache time in seconds', minimum: 0)]
		?int $cacheTime = null,
		#[Schema(description: 'Newsfeed ordering')]
		?int $ordering = null,
		#[Schema(description: 'An array of tag IDs to associate with the newsfeed', items: ['type' => 'integer'], minItems: 0, uniqueItems: true)]
		?array $tags = null,
		#[Schema(description: 'Optional meta description')]
		?string $metadesc = null,
		#[Schema(description: 'Optional meta keywords, separated by commas')]
		?string $metakey = null
	)
	{
		$this->autologMCPTool();

		$postData = [
			'name'        => $name,
			'link'        => $link,
			'catid'       => $catId,
			'alias'       => $alias,
			'published'   => $published,
			'access'      => $access,
			'language'    => $language,
			'description' => $description,
			'numarticles' => $numArticles,
			'cache_time'  => $cacheTime,
			'ordering'    => $ordering,
			'tags'        => $tags,
			'metadesc'    => $metadesc,
			'metakey'     => $metakey,
		];

		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/newsfeeds/feeds/' . $id);

		$response = $http->patch($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'newsfeeds');
	}

	#[McpTool(
		name: 'newsfeeds_delete',
		description: 'Permanently deletes a newsfeed. The newsfeed MUST be set to a trashed state (-2) before calling this method.',
		annotations: new ToolAnnotations(destructiveHint: true)
	)]
	public function deleteFeed(
		#[Schema(description: 'The ID of the newsfeed to delete')]
		int $id
	): bool
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/newsfeeds/feeds/' . $id);
		$response = $http->delete($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $response->getStatusCode() === 204;
	}
}

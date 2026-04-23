<?php
/**
 * @package       joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license       AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Dionysopoulos\Mcp4Joomla\Server\Newsfeeds;

use Dionysopoulos\Mcp4Joomla\Container\Factory;
use Dionysopoulos\Mcp4Joomla\Utility\AutoLoggingTrait;
use Dionysopoulos\Mcp4Joomla\Utility\GetDataFromResponseTrait;
use Dionysopoulos\Mcp4Joomla\Utility\HandleJoomlaAPIErrorTrait;
use Dionysopoulos\Mcp4Joomla\Utility\HttpDecorator;
use Dionysopoulos\Mcp4Joomla\Utility\ReadMergeUpdateTrait;
use Dionysopoulos\Mcp4Joomla\Utility\TitleToAliasTrait;
use Dionysopoulos\Mcp4Joomla\Utility\VarToLogTrait;
use PhpMcp\Schema\ToolAnnotations;
use PhpMcp\Server\Attributes\McpTool;
use PhpMcp\Server\Attributes\Schema;

/**
 * MCP elements for Joomla! newsfeed categories management
 */
class Categories
{
	use HandleJoomlaAPIErrorTrait;
	use GetDataFromResponseTrait;
	use VarToLogTrait;
	use AutoLoggingTrait;
	use TitleToAliasTrait;
	use ReadMergeUpdateTrait;

	#[McpTool(
		name: 'newsfeeds_categories_list',
		description: 'List existing newsfeed categories',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function listCategories(
		#[Schema(description: 'Search filter for the category title')]
		?string $filterSearch = null,
		#[Schema(
			description: 'Filter by publish state: 0=unpublished, 1=published, 2=archived, -2=trashed',
			enum: [null, 0, 1, 2, -2]
		)]
		?int $filterState = null,
		#[Schema(description: 'Filter by language code, or "*" for all languages', pattern: '^(\*|[a-z]{2}(-[A-Z]{2})?)$')]
		?string $filterLanguage = null,
		#[Schema(description: 'Filter by category nesting level', minimum: 1)]
		?int $filterLevel = null,
		#[Schema(description: 'Maximum number of items to return per page', minimum: 1)]
		?int $pageLimit = null,
		#[Schema(description: 'Starting record offset for pagination (0-based)', minimum: 0)]
		?int $pageOffset = null
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/newsfeeds/categories');

		if ($filterSearch !== null)
		{
			$uri->setVar('filter[search]', $filterSearch);
		}

		if ($filterState !== null)
		{
			$uri->setVar('filter[published]', $filterState);
		}

		if ($filterLanguage !== null)
		{
			$uri->setVar('filter[language]', $filterLanguage);
		}

		if ($filterLevel !== null)
		{
			$uri->setVar('filter[level]', $filterLevel);
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

		return $this->getDataFromResponse($response, 'categories');
	}

	#[McpTool(
		name: 'newsfeeds_categories_read',
		description: 'Retrieve the information of the specified newsfeed category',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function readCategory(
		#[Schema(description: 'The ID of the category to retrieve')]
		int $id
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/newsfeeds/categories/' . $id);
		$response = $http->get($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'categories');
	}

	#[McpTool(
		name: 'newsfeeds_categories_create',
		description: 'Create a new newsfeed category'
	)]
	public function createCategory(
		#[Schema(description: 'Category title', minLength: 1, maxLength: 255)]
		string $title,
		#[Schema(description: 'URL slug for the category', pattern: '^[a-z0-9-_]+$')]
		?string $alias = null,
		#[Schema(description: 'Parent category ID, 0 for root')]
		?int $parentId = 0,
		#[Schema(
			description: 'Publish state: 0=unpublished, 1=published, 2=archived, -2=trashed',
			enum: [0, 1, 2, -2]
		)]
		int $published = 1,
		#[Schema(description: 'The Joomla! viewing access level for the category')]
		?int $access = null,
		#[Schema(description: 'Language code for the category, or "*" for all languages', pattern: '^(\*|[a-z]{2}(-[A-Z]{2})?)$')]
		string $language = '*',
		#[Schema(description: 'Category description')]
		?string $description = null,
		#[Schema(description: 'An array of tag IDs to associate with the category', items: ['type' => 'integer'], minItems: 0, uniqueItems: true)]
		?array $tags = null,
		#[Schema(description: 'Optional meta description')]
		?string $metadesc = null,
		#[Schema(description: 'Optional meta keywords, separated by commas')]
		?string $metakey = null,
		#[Schema(description: 'Optional note for the category')]
		?string $note = null
	)
	{
		$this->autologMCPTool();

		$postData = [
			'title'       => $title,
			'alias'       => $alias ?: $this->titleToAlias($title),
			'parent_id'   => $parentId,
			'published'   => $published,
			'access'      => $access ?? 1,
			'language'    => $language,
			'description' => $description,
			'tags'        => $tags,
			'metadesc'    => $metadesc,
			'metakey'     => $metakey,
			'note'        => $note,
		];

		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/newsfeeds/categories');

		$response = $http->post($uri->toString(), json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'categories');
	}

	#[McpTool(
		name: 'newsfeeds_categories_update',
		description: 'Update an existing newsfeed category',
		annotations: new ToolAnnotations(idempotentHint: true)
	)]
	public function updateCategory(
		#[Schema(description: 'The ID of the category to modify')]
		int $id,
		#[Schema(description: 'Category title', minLength: 1, maxLength: 255)]
		?string $title = null,
		#[Schema(description: 'URL slug for the category', pattern: '^[a-z0-9-_]+$')]
		?string $alias = null,
		#[Schema(description: 'Parent category ID, 0 for root')]
		?int $parentId = null,
		#[Schema(
			description: 'Publish state: null=no change, 0=unpublished, 1=published, 2=archived, -2=trashed',
			enum: [null, 0, 1, 2, -2]
		)]
		?int $published = null,
		#[Schema(description: 'The Joomla! viewing access level for the category')]
		?int $access = null,
		#[Schema(description: 'Language code for the category, or "*" for all languages', pattern: '^(\*|[a-z]{2}(-[A-Z]{2})?)$')]
		?string $language = null,
		#[Schema(description: 'Category description')]
		?string $description = null,
		#[Schema(description: 'An array of tag IDs to associate with the category', items: ['type' => 'integer'], minItems: 0, uniqueItems: true)]
		?array $tags = null,
		#[Schema(description: 'Optional meta description')]
		?string $metadesc = null,
		#[Schema(description: 'Optional meta keywords, separated by commas')]
		?string $metakey = null,
		#[Schema(description: 'Optional note for the category')]
		?string $note = null
	)
	{
		$this->autologMCPTool();

		$postData = [
			'title'       => $title,
			'alias'       => $alias,
			'parent_id'   => $parentId,
			'published'   => $published,
			'access'      => $access,
			'language'    => $language,
			'description' => $description,
			'tags'        => $tags,
			'metadesc'    => $metadesc,
			'metakey'     => $metakey,
			'note'        => $note,
		];

		$writableFields = array_keys($postData);
		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/newsfeeds/categories/' . $id);

		$postData = $this->prepareReadMergeUpdatePayload($http, (string) $uri, 'categories', $postData, $writableFields);

		$response = $http->patch($uri->toString(), json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'categories');
	}

	#[McpTool(
		name: 'newsfeeds_categories_trash',
		description: 'Moves a newsfeed category to the trash by setting its state to -2',
		annotations: new ToolAnnotations(destructiveHint: true)
	)]
	public function trashCategory(
		#[Schema(description: 'The ID of the category to trash')]
		int $id
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/newsfeeds/categories/' . $id);
		$response = $http->patch($uri->toString(), json_encode(['published' => -2]), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'categories');
	}

	#[McpTool(
		name: 'newsfeeds_categories_delete',
		description: 'Permanently deletes a newsfeed category. Automatically trashes it first if needed.',
		annotations: new ToolAnnotations(destructiveHint: true)
	)]
	public function deleteCategory(
		#[Schema(description: 'The ID of the category to delete')]
		int $id
	): bool
	{
		$this->autologMCPTool();

		$this->trashCategory($id);

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/newsfeeds/categories/' . $id);
		$response = $http->delete($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $response->getStatusCode() === 204;
	}
}

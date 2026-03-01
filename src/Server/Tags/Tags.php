<?php
/**
 * @package       joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license       AGPL-3.0-or-later
 */

namespace Dionysopoulos\Mcp4Joomla\Server\Tags;

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
 * MCP elements for Joomla! tags management
 *
 * @see  ../../../http/README.md for details
 */
class Tags
{
	use HandleJoomlaAPIErrorTrait;
	use GetDataFromResponseTrait;
	use VarToLogTrait;
	use AutoLoggingTrait;
	use TitleToAliasTrait;
	use ReadMergeUpdateTrait;

	#[McpTool(
		name: 'tags_list',
		description: 'List existing tags',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function listTags(
		#[Schema(description: 'Search filter for the tag title')]
		?string $filterSearch = null,
		#[Schema(
			description: 'Filter by publish state: 0=unpublished, 1=published, 2=archived, -2=trashed',
			enum: [null, 0, 1, 2, -2]
		)]
		?int $filterState = null,
		#[Schema(description: 'Filter by language code, or "*" for all languages', pattern: '^(\*|[a-z]{2}(-[A-Z]{2})?)$')]
		?string $filterLanguage = null
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/tags');

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

		$response = $http->get($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'tags');
	}

	#[McpTool(
		name: 'tags_read',
		description: 'Retrieve the information of the specified tag',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function readTag(
		#[Schema(description: 'The ID of the tag to retrieve')]
		int $id
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/tags/' . $id);
		$response = $http->get($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'tags');
	}

	#[McpTool(
		name: 'tags_create',
		description: 'Create a new tag'
	)]
	public function createTag(
		#[Schema(description: 'Tag title', minLength: 1, maxLength: 255)]
		string $title,
		#[Schema(description: 'URL slug for the tag', pattern: '^[a-z0-9-_]+$')]
		?string $alias = null,
		#[Schema(
			description: 'Publish state: 0=unpublished, 1=published, 2=archived, -2=trashed',
			enum: [0, 1, 2, -2]
		)]
		int $published = 1,
		#[Schema(description: 'Parent tag ID, 0 for root')]
		?int $parentId = 0,
		#[Schema(description: 'The Joomla! viewing access level for the tag')]
		?int $access = null,
		#[Schema(description: 'Language code for the tag, or "*" for all languages', pattern: '^(\*|[a-z]{2}(-[A-Z]{2})?)$')]
		string $language = '*',
		#[Schema(description: 'Tag description')]
		?string $description = null,
		#[Schema(description: 'Optional meta description')]
		?string $metadesc = null,
		#[Schema(description: 'Optional meta keywords, separated by commas')]
		?string $metakey = null,
		#[Schema(description: 'Optional note for the tag')]
		?string $note = null
	)
	{
		$this->autologMCPTool();

		$postData = [
			'title'       => $title,
			'alias'       => $alias ?: $this->titleToAlias($title),
			'published'   => $published,
			'parent_id'   => $parentId,
			'access'      => $access ?? 1,
			'language'    => $language,
			'description' => $description,
			'metadesc'    => $metadesc,
			'metakey'     => $metakey,
			'note'        => $note,
		];

		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/tags');

		$response = $http->post($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'tags');
	}

	#[McpTool(
		name: 'tags_update',
		description: 'Update an existing tag',
		annotations: new ToolAnnotations(idempotentHint: true)
	)]
	public function updateTag(
		#[Schema(description: 'The ID of the tag to modify')]
		int $id,
		#[Schema(description: 'Tag title', minLength: 1, maxLength: 255)]
		?string $title = null,
		#[Schema(description: 'URL slug for the tag', pattern: '^[a-z0-9-_]+$')]
		?string $alias = null,
		#[Schema(
			description: 'Publish state: null=no change, 0=unpublished, 1=published, 2=archived, -2=trashed',
			enum: [null, 0, 1, 2, -2]
		)]
		?int $published = null,
		#[Schema(description: 'Parent tag ID, 0 for root')]
		?int $parentId = null,
		#[Schema(description: 'The Joomla! viewing access level for the tag')]
		?int $access = null,
		#[Schema(description: 'Language code for the tag, or "*" for all languages', pattern: '^(\*|[a-z]{2}(-[A-Z]{2})?)$')]
		?string $language = null,
		#[Schema(description: 'Tag description')]
		?string $description = null,
		#[Schema(description: 'Optional meta description')]
		?string $metadesc = null,
		#[Schema(description: 'Optional meta keywords, separated by commas')]
		?string $metakey = null,
		#[Schema(description: 'Optional note for the tag')]
		?string $note = null
	)
	{
		$this->autologMCPTool();

		$postData = [
			'title'       => $title,
			'alias'       => $alias,
			'published'   => $published,
			'parent_id'   => $parentId,
			'access'      => $access,
			'language'    => $language,
			'description' => $description,
			'metadesc'    => $metadesc,
			'metakey'     => $metakey,
			'note'        => $note,
		];

		$writableFields = array_keys($postData);
		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/tags/' . $id);

		$postData = $this->prepareReadMergeUpdatePayload($http, (string) $uri, 'tags', $postData, $writableFields);

		$response = $http->patch($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'tags');
	}

	#[McpTool(
		name: 'tags_trash',
		description: 'Moves a tag to the trash by setting its state to -2',
		annotations: new ToolAnnotations(destructiveHint: true)
	)]
	public function trashTag(
		#[Schema(description: 'The ID of the tag to trash')]
		int $id
	)
	{
		$this->autologMCPTool();

		return $this->updateTag(id: $id, published: -2);
	}

	#[McpTool(
		name: 'tags_delete',
		description: 'Permanently deletes a tag. Automatically trashes it first if needed.',
		annotations: new ToolAnnotations(destructiveHint: true)
	)]
	public function deleteTag(
		#[Schema(description: 'The ID of the tag to delete')]
		int $id
	): bool
	{
		$this->autologMCPTool();

		$this->trashTag($id);

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/tags/' . $id);
		$response = $http->delete($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $response->getStatusCode() === 204;
	}
}

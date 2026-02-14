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
 * MCP elements for Joomla! content language management
 */
class ContentLanguages
{
	use HandleJoomlaAPIErrorTrait;
	use GetDataFromResponseTrait;
	use VarToLogTrait;
	use AutoLoggingTrait;

	#[McpTool(
		name: 'languages_content_list',
		description: 'List existing content languages',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function listLanguages(
		#[Schema(description: 'Filter by published state', enum: [0, 1])]
		?int $filterState = null
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/languages/content');

		if ($filterState !== null)
		{
			$uri->setVar('filter[published]', $filterState);
		}

		$response = $http->get($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'languages');
	}

	#[McpTool(
		name: 'languages_content_read',
		description: 'Retrieve the information of the specified content language',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function readLanguage(
		#[Schema(description: 'The ID of the content language to retrieve')]
		int $id
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/languages/content/' . $id);
		$response = $http->get($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'languages');
	}

	#[McpTool(
		name: 'languages_content_create',
		description: 'Create a new content language'
	)]
	public function createLanguage(
		#[Schema(description: 'The language code, e.g. "en-GB"')]
		string $langCode,
		#[Schema(description: 'The title of the content language')]
		string $title,
		#[Schema(description: 'The native title of the content language')]
		string $titleNative,
		#[Schema(description: 'The SEF tag for the language')]
		?string $sef = null,
		#[Schema(description: 'The flag image for the language')]
		?string $image = null,
		#[Schema(description: 'Published state: 0=unpublished, 1=published', enum: [0, 1])]
		int $published = 1,
		#[Schema(description: 'The viewing access level for the language')]
		?int $access = null,
		#[Schema(description: 'Meta description for the language')]
		?string $metadesc = null,
		#[Schema(description: 'Meta keywords for the language')]
		?string $metakey = null,
		#[Schema(description: 'Site name for this language')]
		?string $sitename = null
	)
	{
		$this->autologMCPTool();

		$postData = [
			'lang_code'    => $langCode,
			'title'        => $title,
			'title_native' => $titleNative,
			'sef'          => $sef,
			'image'        => $image,
			'published'    => $published,
			'access'       => $access ?? 1,
			'metadesc'     => $metadesc,
			'metakey'      => $metakey,
			'sitename'     => $sitename,
		];

		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/languages/content');

		$response = $http->post($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'languages');
	}

	#[McpTool(
		name: 'languages_content_update',
		description: 'Update an existing content language',
		annotations: new ToolAnnotations(idempotentHint: true)
	)]
	public function updateLanguage(
		#[Schema(description: 'The ID of the content language to update')]
		int $id,
		#[Schema(description: 'The language code, e.g. "en-GB"')]
		?string $langCode = null,
		#[Schema(description: 'The title of the content language')]
		?string $title = null,
		#[Schema(description: 'The native title of the content language')]
		?string $titleNative = null,
		#[Schema(description: 'The SEF tag for the language')]
		?string $sef = null,
		#[Schema(description: 'The flag image for the language')]
		?string $image = null,
		#[Schema(description: 'Published state: 0=unpublished, 1=published', enum: [0, 1])]
		?int $published = null,
		#[Schema(description: 'The viewing access level for the language')]
		?int $access = null,
		#[Schema(description: 'Meta description for the language')]
		?string $metadesc = null,
		#[Schema(description: 'Meta keywords for the language')]
		?string $metakey = null,
		#[Schema(description: 'Site name for this language')]
		?string $sitename = null
	)
	{
		$this->autologMCPTool();

		$postData = [
			'lang_code'    => $langCode,
			'title'        => $title,
			'title_native' => $titleNative,
			'sef'          => $sef,
			'image'        => $image,
			'published'    => $published,
			'access'       => $access,
			'metadesc'     => $metadesc,
			'metakey'      => $metakey,
			'sitename'     => $sitename,
		];

		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/languages/content/' . $id);

		$response = $http->patch($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'languages');
	}

	#[McpTool(
		name: 'languages_content_delete',
		description: 'Delete a content language',
		annotations: new ToolAnnotations(destructiveHint: true)
	)]
	public function deleteLanguage(
		#[Schema(description: 'The ID of the content language to delete')]
		int $id
	): bool
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/languages/content/' . $id);
		$response = $http->delete($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $response->getStatusCode() === 204;
	}
}

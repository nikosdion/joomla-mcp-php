<?php
/**
 * @package       joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license       AGPL-3.0-or-later
 */

namespace Dionysopoulos\Mcp4Joomla\Server\Redirects;

use Dionysopoulos\Mcp4Joomla\Container\Factory;
use Dionysopoulos\Mcp4Joomla\Utility\AutoLoggingTrait;
use Dionysopoulos\Mcp4Joomla\Utility\GetDataFromResponseTrait;
use Dionysopoulos\Mcp4Joomla\Utility\HandleJoomlaAPIErrorTrait;
use Dionysopoulos\Mcp4Joomla\Utility\HttpDecorator;
use Dionysopoulos\Mcp4Joomla\Utility\TitleToAliasTrait;
use Dionysopoulos\Mcp4Joomla\Utility\VarToLogTrait;
use PhpMcp\Schema\ToolAnnotations;
use PhpMcp\Server\Attributes\McpTool;

/**
 * MCP elements for Joomla! redirects management
 *
 * @see  ../../../http/README.md for details
 */
class Redirects
{
	use HandleJoomlaAPIErrorTrait;
	use GetDataFromResponseTrait;
	use VarToLogTrait;
	use AutoLoggingTrait;
	use TitleToAliasTrait;

	#[McpTool(
		name: 'redirects_list',
		description: 'List existing redirects',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function listRedirects(
		#[Schema(description: 'Search filter for the redirect URLs')]
		?string $filterSearch = null,
		#[Schema(
			description: 'Filter by publish state: 0=unpublished, 1=published, 2=archived, -2=trashed',
			enum: [null, 0, 1, 2, -2]
		)]
		?int $filterState = null
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/redirects');

		if ($filterSearch !== null)
		{
			$uri->setVar('filter[search]', $filterSearch);
		}

		if ($filterState !== null)
		{
			$uri->setVar('filter[published]', $filterState);
		}

		$response = $http->get($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'redirects');
	}

	#[McpTool(
		name: 'redirects_read',
		description: 'Retrieve the information of the specified redirect',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function readRedirect(
		#[Schema(description: 'The ID of the redirect to retrieve')]
		int $id
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/redirects/' . $id);
		$response = $http->get($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'redirects');
	}

	#[McpTool(
		name: 'redirects_create',
		description: 'Create a new redirect'
	)]
	public function createRedirect(
		#[Schema(description: 'The old URL to redirect from', minLength: 1)]
		string $oldUrl,
		#[Schema(description: 'The new URL to redirect to', minLength: 1)]
		string $newUrl,
		#[Schema(description: 'The HTTP status code for the redirect', enum: [301, 302, 303, 307, 308])]
		?int $statusCode = 301,
		#[Schema(
			description: 'Publish state: 0=unpublished, 1=published, 2=archived, -2=trashed',
			enum: [0, 1, 2, -2]
		)]
		int $published = 1,
		#[Schema(description: 'Optional comment for the redirect')]
		?string $comment = null
	)
	{
		$this->autologMCPTool();

		$postData = [
			'old_url'   => $oldUrl,
			'new_url'   => $newUrl,
			'referer'   => $statusCode,
			'published' => $published,
			'comment'   => $comment,
		];

		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/redirects');

		$response = $http->post($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'redirects');
	}

	#[McpTool(
		name: 'redirects_update',
		description: 'Update an existing redirect',
		annotations: new ToolAnnotations(idempotentHint: true)
	)]
	public function updateRedirect(
		#[Schema(description: 'The ID of the redirect to modify')]
		int $id,
		#[Schema(description: 'The old URL to redirect from', minLength: 1)]
		?string $oldUrl = null,
		#[Schema(description: 'The new URL to redirect to', minLength: 1)]
		?string $newUrl = null,
		#[Schema(description: 'The HTTP status code for the redirect', enum: [null, 301, 302, 303, 307, 308])]
		?int $statusCode = null,
		#[Schema(
			description: 'Publish state: null=no change, 0=unpublished, 1=published, 2=archived, -2=trashed',
			enum: [null, 0, 1, 2, -2]
		)]
		?int $published = null,
		#[Schema(description: 'Optional comment for the redirect')]
		?string $comment = null
	)
	{
		$this->autologMCPTool();

		$postData = [
			'old_url'   => $oldUrl,
			'new_url'   => $newUrl,
			'referer'   => $statusCode,
			'published' => $published,
			'comment'   => $comment,
		];

		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/redirects/' . $id);

		$response = $http->patch($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'redirects');
	}

	#[McpTool(
		name: 'redirects_delete',
		description: 'Permanently deletes a redirect. The redirect MUST be set to a trashed state (-2) before calling this method.',
		annotations: new ToolAnnotations(destructiveHint: true)
	)]
	public function deleteRedirect(
		#[Schema(description: 'The ID of the redirect to delete')]
		int $id
	): bool
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/redirects/' . $id);
		$response = $http->delete($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $response->getStatusCode() === 204;
	}
}

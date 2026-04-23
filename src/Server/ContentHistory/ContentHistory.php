<?php
/**
 * @package       joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license       AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Dionysopoulos\Mcp4Joomla\Server\ContentHistory;

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
 * MCP elements for Joomla! content history management
 */
class ContentHistory
{
	use HandleJoomlaAPIErrorTrait;
	use GetDataFromResponseTrait;
	use VarToLogTrait;
	use AutoLoggingTrait;

	#[McpTool(
		name: 'contenthistory_list',
		description: 'List content history versions for a resource item',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function listHistory(
		#[Schema(description: 'The resource type, e.g. "content/articles"')]
		string $resource,
		#[Schema(description: 'The ID of the item to retrieve history for')]
		int $itemId,
		#[Schema(description: 'Maximum number of items to return per page', minimum: 1)]
		?int $pageLimit = null,
		#[Schema(description: 'Starting record offset for pagination (0-based)', minimum: 0)]
		?int $pageOffset = null
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/' . $resource . '/' . $itemId . '/contenthistory');

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

		return $this->getDataFromResponse($response, 'contenthistory');
	}

	#[McpTool(
		name: 'contenthistory_keep',
		description: 'Toggle the keep flag on a content history version',
		annotations: new ToolAnnotations(idempotentHint: true)
	)]
	public function keepHistory(
		#[Schema(description: 'The resource type, e.g. "content/articles"')]
		string $resource,
		#[Schema(description: 'The ID of the item')]
		int $itemId,
		#[Schema(description: 'The ID of the content history version')]
		int $versionId
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/' . $resource . '/' . $itemId . '/contenthistory/' . $versionId . '/keep');
		$response = $http->patch($uri->toString(), '', ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'contenthistory');
	}

	#[McpTool(
		name: 'contenthistory_delete',
		description: 'Delete a content history version',
		annotations: new ToolAnnotations(destructiveHint: true)
	)]
	public function deleteHistory(
		#[Schema(description: 'The resource type, e.g. "content/articles"')]
		string $resource,
		#[Schema(description: 'The ID of the item')]
		int $itemId,
		#[Schema(description: 'The ID of the content history version to delete')]
		int $versionId
	): bool
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/' . $resource . '/' . $itemId . '/contenthistory/' . $versionId);
		$response = $http->delete($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $response->getStatusCode() === 204;
	}
}

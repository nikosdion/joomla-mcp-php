<?php
/**
 * @package       joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license       AGPL-3.0-or-later
 */

namespace Dionysopoulos\Mcp4Joomla\Server\Media;

use Dionysopoulos\Mcp4Joomla\Container\Factory;
use Dionysopoulos\Mcp4Joomla\Utility\AutoLoggingTrait;
use Dionysopoulos\Mcp4Joomla\Utility\GetDataFromResponseTrait;
use Dionysopoulos\Mcp4Joomla\Utility\HandleJoomlaAPIErrorTrait;
use Dionysopoulos\Mcp4Joomla\Utility\HttpDecorator;
use Dionysopoulos\Mcp4Joomla\Utility\VarToLogTrait;
use PhpMcp\Schema\ToolAnnotations;
use PhpMcp\Server\Attributes\McpTool;

/**
 * MCP elements for Joomla! media management
 */
class Media
{
	use HandleJoomlaAPIErrorTrait;
	use GetDataFromResponseTrait;
	use VarToLogTrait;
	use AutoLoggingTrait;

	#[McpTool(
		name: 'media_adapters_list',
		description: 'List available media adapters',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function listAdapters()
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/media/adapters');
		$response = $http->get($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'media-adapters');
	}

	#[McpTool(
		name: 'media_adapters_read',
		description: 'Retrieve a specific media adapter',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function readAdapter(
		#[Schema(description: 'The ID of the media adapter to retrieve')]
		int $id
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/media/adapters/' . $id);
		$response = $http->get($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'media-adapters');
	}

	#[McpTool(
		name: 'media_files_list',
		description: 'List media files',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function listFiles(
		#[Schema(description: 'Subdirectory path to filter by')]
		?string $path = null,
		#[Schema(description: 'Media adapter to filter by')]
		?string $adapter = null
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/media/files');

		if ($path !== null)
		{
			$uri->setVar('path', $path);
		}

		if ($adapter !== null)
		{
			$uri->setVar('adapter', $adapter);
		}

		$response = $http->get($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'media-files');
	}

	#[McpTool(
		name: 'media_files_read',
		description: 'Retrieve a specific media file',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function readFile(
		#[Schema(description: 'URL-encoded file path')]
		string $path
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/media/files/' . $path);
		$response = $http->get($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'media-files');
	}

	#[McpTool(
		name: 'media_files_create',
		description: 'Create a new media file'
	)]
	public function createFile(
		#[Schema(description: 'Destination path for the file')]
		string $path,
		#[Schema(description: 'Base64-encoded file content')]
		string $content
	)
	{
		$this->autologMCPTool();

		$postData = [
			'path'    => $path,
			'content' => $content,
		];

		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/media/files');

		$response = $http->post($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'media-files');
	}

	#[McpTool(
		name: 'media_files_update',
		description: 'Update an existing media file',
		annotations: new ToolAnnotations(idempotentHint: true)
	)]
	public function updateFile(
		#[Schema(description: 'Path of the file to update')]
		string $path,
		#[Schema(description: 'Base64-encoded new file content')]
		string $content
	)
	{
		$this->autologMCPTool();

		$postData = [
			'content' => $content,
		];

		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/media/files/' . $path);

		$response = $http->patch($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'media-files');
	}

	#[McpTool(
		name: 'media_files_delete',
		description: 'Delete a media file',
		annotations: new ToolAnnotations(destructiveHint: true)
	)]
	public function deleteFile(
		#[Schema(description: 'Path of the file to delete')]
		string $path
	): bool
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/media/files/' . $path);
		$response = $http->delete($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $response->getStatusCode() === 204;
	}
}

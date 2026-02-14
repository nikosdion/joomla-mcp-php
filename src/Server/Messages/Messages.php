<?php
/**
 * @package       joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license       AGPL-3.0-or-later
 */

namespace Dionysopoulos\Mcp4Joomla\Server\Messages;

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
 * MCP elements for Joomla! private messages management
 *
 * @see  ../../../http/README.md for details
 */
class Messages
{
	use HandleJoomlaAPIErrorTrait;
	use GetDataFromResponseTrait;
	use VarToLogTrait;
	use AutoLoggingTrait;

	#[McpTool(
		name: 'messages_list',
		description: 'List existing private messages',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function listMessages(
		#[Schema(
			description: 'Filter by message state: 0=unread, 1=read, -2=trashed',
			enum: [null, 0, 1, -2]
		)]
		?int $filterState = null
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/messages');

		if ($filterState !== null)
		{
			$uri->setVar('filter[state]', $filterState);
		}

		$response = $http->get($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'messages');
	}

	#[McpTool(
		name: 'messages_read',
		description: 'Retrieve the information of the specified private message',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function readMessage(
		#[Schema(description: 'The ID of the message to retrieve')]
		int $id
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/messages/' . $id);
		$response = $http->get($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'messages');
	}

	#[McpTool(
		name: 'messages_create',
		description: 'Create a new private message'
	)]
	public function createMessage(
		#[Schema(description: 'The ID of the Joomla user to send the message to', minimum: 1)]
		int $userIdTo,
		#[Schema(description: 'Message subject', minLength: 1, maxLength: 255)]
		string $subject,
		#[Schema(description: 'Message body text', minLength: 1)]
		string $message
	)
	{
		$this->autologMCPTool();

		$postData = [
			'user_id_to' => $userIdTo,
			'subject'    => $subject,
			'message'    => $message,
		];

		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/messages');

		$response = $http->post($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'messages');
	}

	#[McpTool(
		name: 'messages_update',
		description: 'Update an existing private message',
		annotations: new ToolAnnotations(idempotentHint: true)
	)]
	public function updateMessage(
		#[Schema(description: 'The ID of the message to modify')]
		int $id,
		#[Schema(description: 'The ID of the Joomla user to send the message to', minimum: 1)]
		?int $userIdTo = null,
		#[Schema(description: 'Message subject', minLength: 1, maxLength: 255)]
		?string $subject = null,
		#[Schema(description: 'Message body text', minLength: 1)]
		?string $message = null
	)
	{
		$this->autologMCPTool();

		$postData = [
			'user_id_to' => $userIdTo,
			'subject'    => $subject,
			'message'    => $message,
		];

		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/messages/' . $id);

		$response = $http->patch($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'messages');
	}

	#[McpTool(
		name: 'messages_delete',
		description: 'Permanently deletes a private message. The message MUST be set to a trashed state (-2) before calling this method.',
		annotations: new ToolAnnotations(destructiveHint: true)
	)]
	public function deleteMessage(
		#[Schema(description: 'The ID of the message to delete')]
		int $id
	): bool
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/messages/' . $id);
		$response = $http->delete($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $response->getStatusCode() === 204;
	}
}

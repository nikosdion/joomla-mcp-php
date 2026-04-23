<?php
/**
 * @package       joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license       AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Dionysopoulos\Mcp4Joomla\Server\Tickets;

use Dionysopoulos\Mcp4Joomla\Container\Factory;
use Dionysopoulos\Mcp4Joomla\Utility\AutoLoggingTrait;
use Dionysopoulos\Mcp4Joomla\Utility\GetDataFromResponseTrait;
use Dionysopoulos\Mcp4Joomla\Utility\HandleJoomlaAPIErrorTrait;
use Dionysopoulos\Mcp4Joomla\Utility\HttpDecorator;
use Dionysopoulos\Mcp4Joomla\Utility\VarToLogTrait;
use PhpMcp\Schema\ToolAnnotations;
use PhpMcp\Server\Attributes\McpTool;
use PhpMcp\Server\Attributes\Schema;

class Posts
{
	use HandleJoomlaAPIErrorTrait;
	use GetDataFromResponseTrait;
	use VarToLogTrait;
	use AutoLoggingTrait;

	#[McpTool(
		name: 'tickets_posts_list',
		description: 'List all ATS (Akeeba Ticket System) ticket posts (replies) across all tickets',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function listPosts(
		#[Schema(description: 'Maximum number of items to return per page', minimum: 1)]
		?int $pageLimit = null,
		#[Schema(description: 'Starting record offset for pagination (0-based)', minimum: 0)]
		?int $pageOffset = null
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/ats/posts');

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

		return $this->getDataFromResponse($response, 'posts');
	}

	#[McpTool(
		name: 'tickets_posts_list_for_ticket',
		description: 'List all posts (replies) for a specific ATS (Akeeba Ticket System) ticket',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function listPostsForTicket(
		#[Schema(description: 'The ID of the ticket whose posts to list')]
		int $ticketId
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/ats/tickets/' . $ticketId . '/posts');
		$response = $http->get($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'posts');
	}

	#[McpTool(
		name: 'tickets_posts_read',
		description: 'Read details of a specific ATS (Akeeba Ticket System) ticket post (reply)',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function readPost(
		#[Schema(description: 'The ID of the post to retrieve')]
		int $id
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/ats/posts/' . $id);
		$response = $http->get($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'posts');
	}

	#[McpTool(
		name: 'tickets_posts_create',
		description: 'Create a new post (reply) under an ATS (Akeeba Ticket System) ticket',
		annotations: new ToolAnnotations(destructiveHint: true)
	)]
	public function createPost(
		#[Schema(description: 'The ID of the ticket to reply to')]
		int $ticketId,
		#[Schema(description: 'The reply body as HTML')]
		string $content_html
	)
	{
		$this->autologMCPTool();

		$postData = [
			'ticket_id'    => $ticketId,
			'content_html' => $content_html,
		];

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/ats/tickets/' . $ticketId . '/posts');
		$response = $http->post($uri->toString(), json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'posts');
	}

	#[McpTool(
		name: 'tickets_posts_update',
		description: 'Update the content of an existing ATS (Akeeba Ticket System) ticket post',
		annotations: new ToolAnnotations(idempotentHint: true)
	)]
	public function updatePost(
		#[Schema(description: 'The ID of the post to update')]
		int $id,
		#[Schema(description: 'The new post body as HTML')]
		string $content_html
	)
	{
		$this->autologMCPTool();

		$postData = ['content_html' => $content_html];

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/ats/posts/' . $id);
		$response = $http->patch($uri->toString(), json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'posts');
	}

	#[McpTool(
		name: 'tickets_posts_delete',
		description: 'Delete an ATS (Akeeba Ticket System) ticket post (reply)',
		annotations: new ToolAnnotations(destructiveHint: true)
	)]
	public function deletePost(
		#[Schema(description: 'The ID of the post to delete')]
		int $id
	): bool
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/ats/posts/' . $id);
		$response = $http->delete($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $response->getStatusCode() === 204;
	}
}

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

class Tickets
{
	use HandleJoomlaAPIErrorTrait;
	use GetDataFromResponseTrait;
	use VarToLogTrait;
	use AutoLoggingTrait;

	#[McpTool(
		name: 'tickets_tickets_list',
		description: 'List ATS (Akeeba Ticket System) support tickets',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function listTickets(
		#[Schema(description: 'Search filter for tickets')]
		?string $filterSearch = null,
		#[Schema(description: 'Maximum number of items to return per page', minimum: 1)]
		?int $pageLimit = null,
		#[Schema(description: 'Starting record offset for pagination (0-based)', minimum: 0)]
		?int $pageOffset = null
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/ats/tickets');

		if ($filterSearch !== null)
		{
			$uri->setVar('filter[search]', $filterSearch);
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

		return $this->getDataFromResponse($response, 'tickets');
	}

	#[McpTool(
		name: 'tickets_tickets_read',
		description: 'Read details of a specific ATS (Akeeba Ticket System) support ticket, optionally including its posts',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function readTicket(
		#[Schema(description: 'The ID of the ticket to retrieve')]
		int $id,
		#[Schema(description: 'When true, embeds the ticket posts in the response')]
		bool $includePosts = false
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri = $http->getUri('v1/ats/tickets/' . $id);

		if ($includePosts)
		{
			$uri->setVar('include', 'posts');
		}

		$response = $http->get($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'tickets');
	}

	#[McpTool(
		name: 'tickets_tickets_create',
		description: 'Create a new ATS (Akeeba Ticket System) support ticket',
		annotations: new ToolAnnotations(destructiveHint: true)
	)]
	public function createTicket(
		#[Schema(description: 'The category ID for the ticket', minimum: 1)]
		int $catid,
		#[Schema(description: 'The subject/title of the ticket', minLength: 1)]
		string $title,
		#[Schema(description: 'The opening message body as HTML')]
		string $content_html,
		#[Schema(description: 'The priority level of the ticket (when ticket priorities are enabled)')]
		?int $priority = null,
		#[Schema(description: 'Whether the ticket is publicly visible (0 = private, 1 = public)', enum: [0, 1])]
		?int $public = null
	)
	{
		$this->autologMCPTool();

		$postData = [
			'catid'        => $catid,
			'title'        => $title,
			'content_html' => $content_html,
			'priority'     => $priority,
			'public'       => $public,
		];

		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/ats/tickets');
		$response = $http->post($uri->toString(), json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'tickets');
	}

	#[McpTool(
		name: 'tickets_tickets_update',
		description: 'Update an existing ATS (Akeeba Ticket System) support ticket',
		annotations: new ToolAnnotations(idempotentHint: true)
	)]
	public function updateTicket(
		#[Schema(description: 'The ID of the ticket to update')]
		int $id,
		#[Schema(description: 'The new subject/title of the ticket')]
		?string $title = null,
		#[Schema(description: 'The new status of the ticket', enum: ['O', 'P', 'C'])]
		?string $status = null
	)
	{
		$this->autologMCPTool();

		$postData = [
			'title'  => $title,
			'status' => $status,
		];

		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/ats/tickets/' . $id);
		$response = $http->patch($uri->toString(), json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'tickets');
	}

	#[McpTool(
		name: 'tickets_tickets_delete',
		description: 'Delete an ATS (Akeeba Ticket System) support ticket',
		annotations: new ToolAnnotations(destructiveHint: true)
	)]
	public function deleteTicket(
		#[Schema(description: 'The ID of the ticket to delete')]
		int $id
	): bool
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/ats/tickets/' . $id);
		$response = $http->delete($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $response->getStatusCode() === 204;
	}
}

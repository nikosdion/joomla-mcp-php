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
		#[Schema(description: 'Filter by category ID')]
		?int $filterCategory = null,
		#[Schema(description: 'Filter by ticket status', enum: [null, 'O', 'P', 'C'])]
		?string $filterStatus = null,
		#[Schema(description: 'Filter by the ID of the user who created the ticket')]
		?int $filterUser = null,
		#[Schema(description: 'Filter by priority ID')]
		?int $filterPriority = null,
		#[Schema(description: 'Field to order results by', enum: [null, 'id', 'title', 'status', 'priority', 'catid', 'created_by', 'created_on', 'modified_on'])]
		?string $orderBy = null,
		#[Schema(description: 'Sort direction', enum: [null, 'ASC', 'DESC'])]
		?string $orderDirection = null,
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

		if ($filterCategory !== null)
		{
			$uri->setVar('filter[catid]', $filterCategory);
		}

		if ($filterStatus !== null)
		{
			$uri->setVar('filter[status]', $filterStatus);
		}

		if ($filterUser !== null)
		{
			$uri->setVar('filter[created_by]', $filterUser);
		}

		if ($filterPriority !== null)
		{
			$uri->setVar('filter[priority]', $filterPriority);
		}

		if ($orderBy !== null)
		{
			$uri->setVar('list[ordering]', $orderBy);
		}

		if ($orderDirection !== null)
		{
			$uri->setVar('list[direction]', $orderDirection);
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
		name: 'tickets_tickets_open',
		description: 'List open ATS (Akeeba Ticket System) support tickets, sorted by most recently modified first',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function listOpenTickets(
		#[Schema(description: 'Search filter for tickets')]
		?string $filterSearch = null,
		#[Schema(description: 'Filter by category ID')]
		?int $filterCategory = null,
		#[Schema(description: 'Filter by the ID of the user who created the ticket')]
		?int $filterUser = null,
		#[Schema(description: 'Filter by priority ID')]
		?int $filterPriority = null,
		#[Schema(description: 'Maximum number of items to return per page', minimum: 1)]
		?int $pageLimit = null,
		#[Schema(description: 'Starting record offset for pagination (0-based)', minimum: 0)]
		?int $pageOffset = null
	)
	{
		$this->autologMCPTool();

		return $this->listTickets(
			filterSearch: $filterSearch,
			filterCategory: $filterCategory,
			filterStatus: 'O',
			filterUser: $filterUser,
			filterPriority: $filterPriority,
			orderBy: 'modified_on',
			orderDirection: 'DESC',
			pageLimit: $pageLimit,
			pageOffset: $pageOffset,
		);
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
		?int $public = null,
		#[Schema(description: 'When the ticket was originally created', format: 'date-time')]
		?string $created = null,
		#[Schema(description: 'The ID of the Joomla user who originally created the ticket')]
		?int $created_by = null,
		#[Schema(description: 'When the ticket was last modified', format: 'date-time')]
		?string $modified = null,
		#[Schema(description: 'The ID of the Joomla user who last modified the ticket')]
		?int $modified_by = null
	)
	{
		$this->autologMCPTool();

		$postData = [
			'catid'        => $catid,
			'title'        => $title,
			'content_html' => $content_html,
			'priority'     => $priority,
			'public'       => $public,
			'created'      => $created,
			'created_by'   => $created_by,
			'modified'     => $modified,
			'modified_by'  => $modified_by,
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
		?string $status = null,
		#[Schema(description: 'When the ticket was originally created', format: 'date-time')]
		?string $created = null,
		#[Schema(description: 'The ID of the Joomla user who originally created the ticket')]
		?int $created_by = null,
		#[Schema(description: 'When the ticket was last modified', format: 'date-time')]
		?string $modified = null,
		#[Schema(description: 'The ID of the Joomla user who last modified the ticket')]
		?int $modified_by = null
	)
	{
		$this->autologMCPTool();

		$postData = [
			'title'       => $title,
			'status'      => $status,
			'created'     => $created,
			'created_by'  => $created_by,
			'modified'    => $modified,
			'modified_by' => $modified_by,
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

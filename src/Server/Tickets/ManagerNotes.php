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

/**
 * Manager note tools for ATS (Akeeba Ticket System). Requires ATS Pro.
 */
class ManagerNotes
{
	use HandleJoomlaAPIErrorTrait;
	use GetDataFromResponseTrait;
	use VarToLogTrait;
	use AutoLoggingTrait;

	#[McpTool(
		name: 'tickets_notes_list_for_ticket',
		description: 'List all internal manager notes for an ATS (Akeeba Ticket System) ticket. Requires ATS Pro.',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function listNotesForTicket(
		#[Schema(description: 'The ID of the ticket whose manager notes to list')]
		int $ticketId
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/ats/tickets/' . $ticketId . '/notes');
		$response = $http->get($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'notes');
	}

	#[McpTool(
		name: 'tickets_notes_read',
		description: 'Read details of a specific ATS (Akeeba Ticket System) manager note. Requires ATS Pro.',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function readNote(
		#[Schema(description: 'The ID of the manager note to retrieve')]
		int $id
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/ats/notes/' . $id);
		$response = $http->get($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'notes');
	}

	#[McpTool(
		name: 'tickets_notes_create',
		description: 'Create an internal manager note on an ATS (Akeeba Ticket System) ticket. Requires ATS Pro.',
		annotations: new ToolAnnotations(destructiveHint: true)
	)]
	public function createNote(
		#[Schema(description: 'The ID of the ticket to add a manager note to')]
		int $ticketId,
		#[Schema(description: 'The note body as HTML')]
		string $note_html
	)
	{
		$this->autologMCPTool();

		$postData = [
			'ticket_id' => $ticketId,
			'note_html' => $note_html,
		];

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/ats/tickets/' . $ticketId . '/notes');
		$response = $http->post($uri->toString(), json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'notes');
	}

	#[McpTool(
		name: 'tickets_notes_delete',
		description: 'Delete an ATS (Akeeba Ticket System) manager note. Requires ATS Pro.',
		annotations: new ToolAnnotations(destructiveHint: true)
	)]
	public function deleteNote(
		#[Schema(description: 'The ID of the manager note to delete')]
		int $id
	): bool
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/ats/notes/' . $id);
		$response = $http->delete($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $response->getStatusCode() === 204;
	}
}

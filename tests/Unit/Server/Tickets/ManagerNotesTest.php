<?php
/**
 * @package   joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license   AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Dionysopoulos\Mcp4Joomla\Tests\Unit\Server\Tickets;

use Dionysopoulos\Mcp4Joomla\Server\Tickets\ManagerNotes;
use Dionysopoulos\Mcp4Joomla\Tests\Unit\Stubs\TestContainerTrait;
use PHPUnit\Framework\TestCase;

class ManagerNotesTest extends TestCase
{
	use TestContainerTrait;

	private ManagerNotes $managerNotes;

	protected function setUp(): void
	{
		$this->setUpTestContainer();
		$this->managerNotes = new ManagerNotes();
	}

	protected function tearDown(): void
	{
		$this->tearDownTestContainer();
	}

	public function testListNotesForTicket(): void
	{
		$body = json_encode([
			'data' => [
				['type' => 'notes', 'id' => '1', 'attributes' => ['note_html' => '<p>Internal note</p>']],
			],
		]);

		$this->mockHttp
			->expects($this->once())
			->method('get')
			->with($this->callback(fn($url) => str_contains((string) $url, 'v1/ats/tickets/10/notes')))
			->willReturn(createJoomlaResponse(200, $body));

		$result = $this->managerNotes->listNotesForTicket(10);

		$this->assertIsObject($result);
		$this->assertCount(1, $result->data);
		$this->assertSame('notes', $result->data[0]->type);
	}

	public function testReadNote(): void
	{
		$body = json_encode([
			'data' => ['type' => 'notes', 'id' => '3', 'attributes' => ['note_html' => '<p>Note</p>']],
		]);

		$this->mockHttp
			->expects($this->once())
			->method('get')
			->with($this->callback(fn($url) => str_contains((string) $url, 'v1/ats/notes/3')))
			->willReturn(createJoomlaResponse(200, $body));

		$result = $this->managerNotes->readNote(3);

		$this->assertIsObject($result);
		$this->assertSame('notes', $result->data->type);
	}

	public function testCreateNote(): void
	{
		$body = json_encode([
			'data' => ['type' => 'notes', 'id' => '20', 'attributes' => ['note_html' => '<p>Manager note</p>']],
		]);

		$this->mockHttp
			->expects($this->once())
			->method('post')
			->with(
				$this->callback(fn($url) => str_contains((string) $url, 'v1/ats/tickets/10/notes')),
				$this->callback(function ($payload) {
					$data = json_decode($payload, true);
					return $data['ticket_id'] === 10 && $data['note_html'] === '<p>Manager note</p>';
				}),
				$this->anything()
			)
			->willReturn(createJoomlaResponse(201, $body));

		$result = $this->managerNotes->createNote(10, '<p>Manager note</p>');

		$this->assertIsObject($result);
		$this->assertSame('notes', $result->data->type);
	}

	public function testDeleteNote(): void
	{
		$this->mockHttp
			->expects($this->once())
			->method('delete')
			->with($this->callback(fn($url) => str_contains((string) $url, 'v1/ats/notes/20')))
			->willReturn(createJoomlaResponse(204));

		$result = $this->managerNotes->deleteNote(20);

		$this->assertTrue($result);
	}
}

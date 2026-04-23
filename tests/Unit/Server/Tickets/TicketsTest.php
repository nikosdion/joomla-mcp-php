<?php
/**
 * @package   joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license   AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Dionysopoulos\Mcp4Joomla\Tests\Unit\Server\Tickets;

use Dionysopoulos\Mcp4Joomla\Server\Tickets\Tickets;
use Dionysopoulos\Mcp4Joomla\Tests\Unit\Stubs\TestContainerTrait;
use PHPUnit\Framework\TestCase;

class TicketsTest extends TestCase
{
	use TestContainerTrait;

	private Tickets $tickets;

	protected function setUp(): void
	{
		$this->setUpTestContainer();
		$this->tickets = new Tickets();
	}

	protected function tearDown(): void
	{
		$this->tearDownTestContainer();
	}

	public function testListTickets(): void
	{
		$body = json_encode([
			'data' => [
				['type' => 'tickets', 'id' => '1', 'attributes' => ['title' => 'Test ticket']],
			],
		]);

		$this->mockHttp
			->expects($this->once())
			->method('get')
			->with($this->callback(fn($url) => str_contains((string) $url, 'v1/ats/tickets')))
			->willReturn(createJoomlaResponse(200, $body));

		$result = $this->tickets->listTickets();

		$this->assertIsObject($result);
		$this->assertCount(1, $result->data);
		$this->assertSame('tickets', $result->data[0]->type);
	}

	public function testListTicketsWithFilters(): void
	{
		$body = json_encode(['data' => []]);

		$this->mockHttp
			->expects($this->once())
			->method('get')
			->with($this->callback(fn($url) => str_contains((string) $url, 'filter[search]=test')))
			->willReturn(createJoomlaResponse(200, $body));

		$result = $this->tickets->listTickets(filterSearch: 'test', pageLimit: 10, pageOffset: 0);

		$this->assertIsObject($result);
		$this->assertIsArray($result->data);
	}

	public function testReadTicket(): void
	{
		$body = json_encode([
			'data' => ['type' => 'tickets', 'id' => '42', 'attributes' => ['title' => 'My ticket']],
		]);

		$this->mockHttp
			->expects($this->once())
			->method('get')
			->with($this->callback(fn($url) => str_contains((string) $url, 'v1/ats/tickets/42')))
			->willReturn(createJoomlaResponse(200, $body));

		$result = $this->tickets->readTicket(42);

		$this->assertIsObject($result);
		$this->assertSame('tickets', $result->data->type);
	}

	public function testReadTicketWithIncludePosts(): void
	{
		$body = json_encode([
			'data' => ['type' => 'tickets', 'id' => '42', 'attributes' => ['title' => 'My ticket']],
		]);

		$this->mockHttp
			->expects($this->once())
			->method('get')
			->with($this->callback(
				fn($url) => str_contains((string) $url, 'v1/ats/tickets/42')
					&& str_contains((string) $url, 'include=posts')
			))
			->willReturn(createJoomlaResponse(200, $body));

		$result = $this->tickets->readTicket(42, includePosts: true);

		$this->assertIsObject($result);
	}

	public function testCreateTicket(): void
	{
		$body = json_encode([
			'data' => ['type' => 'tickets', 'id' => '99', 'attributes' => ['title' => 'New ticket']],
		]);

		$this->mockHttp
			->expects($this->once())
			->method('post')
			->with(
				$this->callback(fn($url) => str_contains((string) $url, 'v1/ats/tickets')),
				$this->callback(function ($payload) {
					$data = json_decode($payload, true);
					return $data['catid'] === 5
						&& $data['title'] === 'New ticket'
						&& $data['content_html'] === '<p>Hello</p>'
						&& !array_key_exists('priority', $data)
						&& !array_key_exists('public', $data);
				}),
				$this->anything()
			)
			->willReturn(createJoomlaResponse(201, $body));

		$result = $this->tickets->createTicket(5, 'New ticket', '<p>Hello</p>');

		$this->assertIsObject($result);
		$this->assertSame('tickets', $result->data->type);
	}

	public function testCreateTicketWithOptionalFields(): void
	{
		$body = json_encode([
			'data' => ['type' => 'tickets', 'id' => '100', 'attributes' => ['title' => 'Priority ticket']],
		]);

		$this->mockHttp
			->expects($this->once())
			->method('post')
			->with(
				$this->anything(),
				$this->callback(function ($payload) {
					$data = json_decode($payload, true);
					return $data['priority'] === 10 && $data['public'] === 1;
				}),
				$this->anything()
			)
			->willReturn(createJoomlaResponse(201, $body));

		$result = $this->tickets->createTicket(5, 'Priority ticket', '<p>Body</p>', priority: 10, public: 1);

		$this->assertIsObject($result);
	}

	public function testUpdateTicket(): void
	{
		$body = json_encode([
			'data' => ['type' => 'tickets', 'id' => '42', 'attributes' => ['title' => 'Updated', 'status' => 'C']],
		]);

		$this->mockHttp
			->expects($this->once())
			->method('patch')
			->with(
				$this->callback(fn($url) => str_contains((string) $url, 'v1/ats/tickets/42')),
				$this->callback(function ($payload) {
					$data = json_decode($payload, true);
					return $data['title'] === 'Updated' && $data['status'] === 'C';
				}),
				$this->anything()
			)
			->willReturn(createJoomlaResponse(200, $body));

		$result = $this->tickets->updateTicket(42, title: 'Updated', status: 'C');

		$this->assertIsObject($result);
	}

	public function testUpdateTicketExcludesNullFields(): void
	{
		$body = json_encode([
			'data' => ['type' => 'tickets', 'id' => '42', 'attributes' => ['status' => 'P']],
		]);

		$this->mockHttp
			->expects($this->once())
			->method('patch')
			->with(
				$this->anything(),
				$this->callback(function ($payload) {
					$data = json_decode($payload, true);
					return $data['status'] === 'P' && !array_key_exists('title', $data);
				}),
				$this->anything()
			)
			->willReturn(createJoomlaResponse(200, $body));

		$result = $this->tickets->updateTicket(42, status: 'P');

		$this->assertIsObject($result);
	}

	public function testDeleteTicket(): void
	{
		$this->mockHttp
			->expects($this->once())
			->method('delete')
			->with($this->callback(fn($url) => str_contains((string) $url, 'v1/ats/tickets/42')))
			->willReturn(createJoomlaResponse(204));

		$result = $this->tickets->deleteTicket(42);

		$this->assertTrue($result);
	}
}

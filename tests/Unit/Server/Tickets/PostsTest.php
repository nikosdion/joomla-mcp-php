<?php
/**
 * @package   joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license   AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Dionysopoulos\Mcp4Joomla\Tests\Unit\Server\Tickets;

use Dionysopoulos\Mcp4Joomla\Server\Tickets\Posts;
use Dionysopoulos\Mcp4Joomla\Tests\Unit\Stubs\TestContainerTrait;
use PHPUnit\Framework\TestCase;

class PostsTest extends TestCase
{
	use TestContainerTrait;

	private Posts $posts;

	protected function setUp(): void
	{
		$this->setUpTestContainer();
		$this->posts = new Posts();
	}

	protected function tearDown(): void
	{
		$this->tearDownTestContainer();
	}

	public function testListPosts(): void
	{
		$body = json_encode([
			'data' => [
				['type' => 'posts', 'id' => '1', 'attributes' => ['content_html' => '<p>Hello</p>']],
			],
		]);

		$this->mockHttp
			->expects($this->once())
			->method('get')
			->with($this->callback(fn($url) => str_contains((string) $url, 'v1/ats/posts')))
			->willReturn(createJoomlaResponse(200, $body));

		$result = $this->posts->listPosts();

		$this->assertIsObject($result);
		$this->assertCount(1, $result->data);
		$this->assertSame('posts', $result->data[0]->type);
	}

	public function testListPostsForTicket(): void
	{
		$body = json_encode([
			'data' => [
				['type' => 'posts', 'id' => '5', 'attributes' => ['content_html' => '<p>Reply</p>']],
			],
		]);

		$this->mockHttp
			->expects($this->once())
			->method('get')
			->with($this->callback(fn($url) => str_contains((string) $url, 'v1/ats/tickets/10/posts')))
			->willReturn(createJoomlaResponse(200, $body));

		$result = $this->posts->listPostsForTicket(10);

		$this->assertIsObject($result);
		$this->assertCount(1, $result->data);
	}

	public function testReadPost(): void
	{
		$body = json_encode([
			'data' => ['type' => 'posts', 'id' => '7', 'attributes' => ['content_html' => '<p>Single</p>']],
		]);

		$this->mockHttp
			->expects($this->once())
			->method('get')
			->with($this->callback(fn($url) => str_contains((string) $url, 'v1/ats/posts/7')))
			->willReturn(createJoomlaResponse(200, $body));

		$result = $this->posts->readPost(7);

		$this->assertIsObject($result);
		$this->assertSame('posts', $result->data->type);
	}

	public function testCreatePost(): void
	{
		$body = json_encode([
			'data' => ['type' => 'posts', 'id' => '50', 'attributes' => ['content_html' => '<p>New reply</p>']],
		]);

		$this->mockHttp
			->expects($this->once())
			->method('post')
			->with(
				$this->callback(fn($url) => str_contains((string) $url, 'v1/ats/tickets/10/posts')),
				$this->callback(function ($payload) {
					$data = json_decode($payload, true);
					return $data['ticket_id'] === 10 && $data['content_html'] === '<p>New reply</p>';
				}),
				$this->anything()
			)
			->willReturn(createJoomlaResponse(201, $body));

		$result = $this->posts->createPost(10, '<p>New reply</p>');

		$this->assertIsObject($result);
		$this->assertSame('posts', $result->data->type);
	}

	public function testUpdatePost(): void
	{
		$body = json_encode([
			'data' => ['type' => 'posts', 'id' => '50', 'attributes' => ['content_html' => '<p>Updated</p>']],
		]);

		$this->mockHttp
			->expects($this->once())
			->method('patch')
			->with(
				$this->callback(fn($url) => str_contains((string) $url, 'v1/ats/posts/50')),
				$this->callback(function ($payload) {
					$data = json_decode($payload, true);
					return $data['content_html'] === '<p>Updated</p>';
				}),
				$this->anything()
			)
			->willReturn(createJoomlaResponse(200, $body));

		$result = $this->posts->updatePost(50, '<p>Updated</p>');

		$this->assertIsObject($result);
	}

	public function testDeletePost(): void
	{
		$this->mockHttp
			->expects($this->once())
			->method('delete')
			->with($this->callback(fn($url) => str_contains((string) $url, 'v1/ats/posts/50')))
			->willReturn(createJoomlaResponse(204));

		$result = $this->posts->deletePost(50);

		$this->assertTrue($result);
	}
}

<?php
/**
 * @package   joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license   AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Dionysopoulos\Mcp4Joomla\Tests\Unit\Server\Tags;

use Dionysopoulos\Mcp4Joomla\Server\Tags\Tags;
use Dionysopoulos\Mcp4Joomla\Tests\Unit\Stubs\TestContainerTrait;
use PHPUnit\Framework\TestCase;

class TagsTest extends TestCase
{
	use TestContainerTrait;

	private Tags $tags;

	protected function setUp(): void
	{
		$this->setUpTestContainer();
		$this->tags = new Tags();
	}

	protected function tearDown(): void
	{
		$this->tearDownTestContainer();
	}

	public function testListTagsNoFilters(): void
	{
		$body = json_encode([
			'data' => [
				['type' => 'tags', 'id' => '1', 'attributes' => ['title' => 'Test Tag']],
			],
		]);

		$this->mockHttp
			->expects($this->once())
			->method('get')
			->with($this->callback(function ($url) {
				return str_contains((string) $url, 'v1/tags');
			}))
			->willReturn(createJoomlaResponse(200, $body));

		$result = $this->tags->listTags();

		$this->assertIsObject($result);
		$this->assertCount(1, $result->data);
		$this->assertSame('tags', $result->data[0]->type);
	}

	public function testListTagsWithFilters(): void
	{
		$body = json_encode(['data' => []]);

		$this->mockHttp
			->expects($this->once())
			->method('get')
			->with($this->callback(function ($url) {
				$urlStr = (string) $url;
				return str_contains($urlStr, 'filter[search]=test')
					&& str_contains($urlStr, 'filter[published]=1');
			}))
			->willReturn(createJoomlaResponse(200, $body));

		$result = $this->tags->listTags(
			filterSearch: 'test',
			filterState: 1
		);

		$this->assertIsObject($result);
		$this->assertIsArray($result->data);
	}

	public function testReadTag(): void
	{
		$body = json_encode([
			'data' => [
				'type'       => 'tags',
				'id'         => '5',
				'attributes' => ['title' => 'Test Tag'],
			],
		]);

		$this->mockHttp
			->expects($this->once())
			->method('get')
			->with($this->callback(function ($url) {
				return str_contains((string) $url, 'v1/tags/5');
			}))
			->willReturn(createJoomlaResponse(200, $body));

		$result = $this->tags->readTag(5);

		$this->assertIsObject($result);
		$this->assertSame('tags', $result->data->type);
	}

	public function testCreateTagGeneratesAlias(): void
	{
		$body = json_encode([
			'data' => [
				'type' => 'tags',
				'id'   => '10',
			],
		]);

		$this->mockHttp
			->expects($this->once())
			->method('post')
			->with(
				$this->callback(function ($url) {
					return str_contains((string) $url, 'v1/tags');
				}),
				$this->callback(function ($data) {
					$decoded = json_decode($data, true);
					return $decoded['alias'] === 'my-new-tag'
						&& $decoded['title'] === 'My New Tag';
				}),
				$this->anything(),
				$this->anything()
			)
			->willReturn(createJoomlaResponse(201, $body));

		$result = $this->tags->createTag(title: 'My New Tag');

		$this->assertIsObject($result);
		$this->assertSame('10', $result->data->id);
	}

	public function testUpdateTagReadsRecordAndMergesChanges(): void
	{
		$readBody = json_encode([
			'data' => [
				'type'       => 'tags',
				'id'         => '5',
				'attributes' => [
					'title'       => 'Existing Tag',
					'alias'       => 'existing-tag',
					'published'   => 1,
					'parent_id'   => 12,
					'access'      => 1,
					'language'    => '*',
					'description' => 'Existing description',
					'metadesc'    => 'Existing metadesc',
					'metakey'     => 'foo,bar',
					'note'        => 'Existing note',
				],
			],
		]);

		$updateBody = json_encode([
			'data' => [
				'type' => 'tags',
				'id'   => '5',
			],
		]);

		$this->mockHttp
			->expects($this->once())
			->method('get')
			->with($this->callback(function ($url) {
				return str_contains((string) $url, 'v1/tags/5');
			}))
			->willReturn(createJoomlaResponse(200, $readBody));

		$this->mockHttp
			->expects($this->once())
			->method('patch')
			->with(
				$this->callback(function ($url) {
					return str_contains((string) $url, 'v1/tags/5');
				}),
				$this->callback(function ($data) {
					$decoded = json_decode($data, true);
					return $decoded['title'] === 'Updated Tag'
						&& $decoded['alias'] === 'existing-tag'
						&& $decoded['parent_id'] === 12;
				}),
				$this->anything(),
				$this->anything()
			)
			->willReturn(createJoomlaResponse(200, $updateBody));

		$this->tags->updateTag(id: 5, title: 'Updated Tag');
	}

	public function testDeleteTagReturnsTrue(): void
	{
		$trashBody = json_encode(['data' => ['type' => 'tags', 'id' => '5', 'attributes' => ['published' => -2]]]);

		$this->mockHttp
			->expects($this->once())
			->method('patch')
			->willReturn(createJoomlaResponse(200, $trashBody));

		$this->mockHttp
			->expects($this->once())
			->method('delete')
			->with($this->callback(function ($url) {
				return str_contains((string) $url, 'v1/tags/5');
			}))
			->willReturn(createJoomlaResponse(204));

		$result = $this->tags->deleteTag(5);

		$this->assertTrue($result);
	}

	public function testDeleteTagReturnsFalseOnNon204(): void
	{
		$trashBody = json_encode(['data' => ['type' => 'tags', 'id' => '5', 'attributes' => ['published' => -2]]]);

		$this->mockHttp
			->expects($this->once())
			->method('patch')
			->willReturn(createJoomlaResponse(200, $trashBody));

		$this->mockHttp
			->expects($this->once())
			->method('delete')
			->willReturn(createJoomlaResponse(200, '{"data":{}}'));

		$result = $this->tags->deleteTag(5);

		$this->assertFalse($result);
	}
}

<?php
/**
 * @package   joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license   AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Dionysopoulos\Mcp4Joomla\Tests\Integration\Tags;

use Dionysopoulos\Mcp4Joomla\Server\Tags\Tags;
use Dionysopoulos\Mcp4Joomla\Tests\Integration\IntegrationTestCase;

class TagsIntegrationTest extends IntegrationTestCase
{
	private Tags $tags;

	/** @var int[] IDs of tags created during the test, for cleanup */
	private array $createdIds = [];

	protected function setUp(): void
	{
		parent::setUp();
		$this->tags = new Tags();
	}

	protected function tearDown(): void
	{
		foreach (array_reverse($this->createdIds) as $id)
		{
			try
			{
				$this->tags->updateTag($id, published: -2);
				$this->tags->deleteTag($id);
			}
			catch (\Throwable)
			{
				// Best-effort cleanup
			}
		}

		parent::tearDown();
	}

	public function testListTags(): void
	{
		$result = $this->tags->listTags();

		$this->assertIsObject($result);
		$this->assertObjectHasProperty('data', $result);
		$this->assertIsArray($result->data);
	}

	public function testCrudLifecycle(): void
	{
		// Create
		$created = $this->tags->createTag(
			title: 'MCP Test Tag ' . time(),
			published: 0,
		);

		$this->assertIsObject($created);
		$this->assertObjectHasProperty('data', $created);
		$tagId              = (int) $created->data->id;
		$this->createdIds[] = $tagId;
		$this->assertGreaterThan(0, $tagId);

		// Read
		$read = $this->tags->readTag($tagId);
		$this->assertIsObject($read);
		$this->assertSame((string) $tagId, $read->data->id);

		// Update
		$updated = $this->tags->updateTag(
			id: $tagId,
			title: 'MCP Test Tag Updated ' . time()
		);
		$this->assertIsObject($updated);

		// Delete (trash first, then delete)
		$this->tags->updateTag($tagId, published: -2);
		$deleted = $this->tags->deleteTag($tagId);
		$this->assertTrue($deleted);

		$this->createdIds = array_diff($this->createdIds, [$tagId]);
	}
}

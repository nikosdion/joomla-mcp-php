<?php
/**
 * @package   joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license   AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Dionysopoulos\Mcp4Joomla\Tests\Integration\Content;

use Dionysopoulos\Mcp4Joomla\Server\Content\Articles;
use Dionysopoulos\Mcp4Joomla\Tests\Integration\IntegrationTestCase;

class ArticlesIntegrationTest extends IntegrationTestCase
{
	private Articles $articles;

	/** @var int[] IDs of articles created during the test, for cleanup */
	private array $createdIds = [];

	protected function setUp(): void
	{
		parent::setUp();
		$this->articles = new Articles();
	}

	protected function tearDown(): void
	{
		// Clean up any articles created during the test
		foreach (array_reverse($this->createdIds) as $id)
		{
			try
			{
				$this->articles->updateArticle($id, state: -2);
				$this->articles->deleteArticle($id);
			}
			catch (\Throwable)
			{
				// Best-effort cleanup
			}
		}

		parent::tearDown();
	}

	public function testListArticles(): void
	{
		$result = $this->articles->listArticles();

		$this->assertIsObject($result);
		$this->assertObjectHasProperty('data', $result);
		$this->assertIsArray($result->data);
	}

	public function testCrudLifecycle(): void
	{
		// Create
		$created = $this->articles->createArticle(
			title: 'MCP Test Article ' . time(),
			catId: 2,
			introText: 'Integration test intro text',
			fullText: 'Integration test full text',
			state: 0,
		);

		$this->assertIsObject($created);
		$this->assertObjectHasProperty('data', $created);
		$articleId          = (int) $created->data->id;
		$this->createdIds[] = $articleId;
		$this->assertGreaterThan(0, $articleId);

		// Read
		$read = $this->articles->readArticle($articleId);
		$this->assertIsObject($read);
		$this->assertSame((string) $articleId, $read->data->id);

		// Update
		$updated = $this->articles->updateArticle(
			articleId: $articleId,
			title: 'MCP Test Article Updated ' . time()
		);
		$this->assertIsObject($updated);

		// Delete (trash first, then delete)
		$this->articles->updateArticle($articleId, state: -2);
		$deleted = $this->articles->deleteArticle($articleId);
		$this->assertTrue($deleted);

		// Remove from cleanup list since we already deleted
		$this->createdIds = array_diff($this->createdIds, [$articleId]);
	}
}

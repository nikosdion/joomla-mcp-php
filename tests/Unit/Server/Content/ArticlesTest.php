<?php
/**
 * @package   joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license   AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Dionysopoulos\Mcp4Joomla\Tests\Unit\Server\Content;

use Dionysopoulos\Mcp4Joomla\Server\Content\Articles;
use Dionysopoulos\Mcp4Joomla\Tests\Unit\Stubs\TestContainerTrait;
use PHPUnit\Framework\TestCase;

class ArticlesTest extends TestCase
{
	use TestContainerTrait;

	private Articles $articles;

	protected function setUp(): void
	{
		$this->setUpTestContainer();
		$this->articles = new Articles();
	}

	protected function tearDown(): void
	{
		$this->tearDownTestContainer();
	}

	public function testListArticlesNoFilters(): void
	{
		$body = json_encode([
			'data' => [
				['type' => 'articles', 'id' => '1', 'attributes' => ['title' => 'Test Article']],
			],
		]);

		$this->mockHttp
			->expects($this->once())
			->method('get')
			->with($this->callback(function ($url) {
				return str_contains((string) $url, 'v1/content/articles');
			}))
			->willReturn(createJoomlaResponse(200, $body));

		$result = $this->articles->listArticles();

		$this->assertIsObject($result);
		$this->assertCount(1, $result->data);
		$this->assertSame('articles', $result->data[0]->type);
	}

	public function testListArticlesWithFilters(): void
	{
		$body = json_encode([
			'data' => [],
		]);

		$this->mockHttp
			->expects($this->once())
			->method('get')
			->with($this->callback(function ($url) {
				$urlStr = (string) $url;
				return str_contains($urlStr, 'filter[author]=1')
					&& str_contains($urlStr, 'filter[state]=1');
			}))
			->willReturn(createJoomlaResponse(200, $body));

		$result = $this->articles->listArticles(
			filterAuthor: 1,
			filterState: 1
		);

		$this->assertIsObject($result);
		$this->assertIsArray($result->data);
	}

	public function testReadArticle(): void
	{
		$body = json_encode([
			'data' => [
				'type'       => 'articles',
				'id'         => '42',
				'attributes' => ['title' => 'Test Article'],
			],
		]);

		$this->mockHttp
			->expects($this->once())
			->method('get')
			->with($this->callback(function ($url) {
				return str_contains((string) $url, 'v1/content/articles/42');
			}))
			->willReturn(createJoomlaResponse(200, $body));

		$result = $this->articles->readArticle(42);

		$this->assertIsObject($result);
		$this->assertSame('articles', $result->data->type);
		$this->assertSame('42', $result->data->id);
	}

	public function testCreateArticleGeneratesAlias(): void
	{
		$body = json_encode([
			'data' => [
				'type'       => 'articles',
				'id'         => '99',
				'attributes' => ['title' => 'My New Article'],
			],
		]);

		$this->mockHttp
			->expects($this->once())
			->method('post')
			->with(
				$this->callback(function ($url) {
					return str_contains((string) $url, 'v1/content/articles');
				}),
				$this->callback(function ($data) {
					$decoded = json_decode($data, true);
					// Alias should be auto-generated from title
					return $decoded['alias'] === 'my-new-article'
						&& $decoded['title'] === 'My New Article';
				}),
				$this->anything(),
				$this->anything()
			)
			->willReturn(createJoomlaResponse(201, $body));

		$result = $this->articles->createArticle(
			title: 'My New Article',
			catId: 2,
			introText: 'Intro text here',
			fullText: 'Full text here',
		);

		$this->assertIsObject($result);
		$this->assertSame('99', $result->data->id);
	}

	public function testCreateArticleUsesProvidedAlias(): void
	{
		$body = json_encode([
			'data' => [
				'type' => 'articles',
				'id'   => '99',
			],
		]);

		$this->mockHttp
			->expects($this->once())
			->method('post')
			->with(
				$this->anything(),
				$this->callback(function ($data) {
					$decoded = json_decode($data, true);
					return $decoded['alias'] === 'custom-alias';
				}),
				$this->anything(),
				$this->anything()
			)
			->willReturn(createJoomlaResponse(201, $body));

		$this->articles->createArticle(
			title: 'My New Article',
			catId: 2,
			introText: 'Intro',
			fullText: 'Full',
			alias: 'custom-alias',
		);
	}

	public function testUpdateArticleFiltersNulls(): void
	{
		$body = json_encode([
			'data' => [
				'type' => 'articles',
				'id'   => '42',
			],
		]);

		$this->mockHttp
			->expects($this->once())
			->method('patch')
			->with(
				$this->callback(function ($url) {
					return str_contains((string) $url, 'v1/content/articles/42');
				}),
				$this->callback(function ($data) {
					$decoded = json_decode($data, true);
					// Only the provided fields should be present
					return isset($decoded['title'])
						&& !array_key_exists('catid', $decoded)
						&& !array_key_exists('alias', $decoded);
				}),
				$this->anything(),
				$this->anything()
			)
			->willReturn(createJoomlaResponse(200, $body));

		$this->articles->updateArticle(
			articleId: 42,
			title: 'Updated Title'
		);
	}

	public function testDeleteArticleReturnsTrue(): void
	{
		$this->mockHttp
			->expects($this->once())
			->method('delete')
			->with($this->callback(function ($url) {
				return str_contains((string) $url, 'v1/content/articles/42');
			}))
			->willReturn(createJoomlaResponse(204));

		$result = $this->articles->deleteArticle(42);

		$this->assertTrue($result);
	}

	public function testDeleteArticleReturnsFalseOnNon204(): void
	{
		$this->mockHttp
			->expects($this->once())
			->method('delete')
			->willReturn(createJoomlaResponse(200, '{"data":{}}'));

		$result = $this->articles->deleteArticle(42);

		$this->assertFalse($result);
	}
}

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

	public function testUpdateArticleReadsRecordAndMergesChanges(): void
	{
		$readBody = json_encode([
			'data' => [
				'type'       => 'articles',
				'id'         => '42',
				'attributes' => [
					'title'            => 'Existing Title',
					'alias'            => 'existing-title',
					'catid'            => 2,
					'state'            => 1,
					'created'          => '2025-01-01 00:00:00',
					'created_by'       => 123,
					'created_by_alias' => 'Existing Author',
					'publish_up'       => '2025-01-01 00:00:00',
					'publish_down'     => null,
					'images'           => [
						'image_intro'            => 'images/old-intro.jpg',
						'image_intro_alt'        => 'Old intro alt',
						'float_intro'            => 'left',
						'image_intro_caption'    => 'Old intro caption',
						'image_fulltext'         => 'images/old-full.jpg',
						'image_fulltext_alt'     => 'Old full alt',
						'float_fulltext'         => 'none',
						'image_fulltext_caption' => 'Old full caption',
					],
					'urls'             => [
						'urla'     => 'https://a.example.com',
						'urlatext' => 'URL A',
						'targeta'  => '_blank',
						'urlb'     => '',
						'urlbtext' => '',
						'targetb'  => '',
						'urlc'     => '',
						'urlctext' => '',
						'targetc'  => '',
					],
					'metakey'          => 'foo,bar',
					'metadesc'         => 'Existing meta description',
					'access'           => 1,
					'hits'             => 5,
					'metadata'         => [
						'robots' => 'index, follow',
						'author' => 'Existing Meta Author',
						'rights' => 'All rights reserved',
					],
					'featured'         => 0,
					'language'         => '*',
					'note'             => 'Existing note',
					'tags'             => [7, 8],
					'featured_up'      => null,
					'featured_down'    => null,
					'introtext'        => '<p>Existing intro</p>',
					'fulltext'         => '<p>Existing full</p>',
				],
			],
		]);

		$updateBody = json_encode([
			'data' => [
				'type' => 'articles',
				'id'   => '42',
			],
		]);

		$this->mockHttp
			->expects($this->once())
			->method('get')
			->with($this->callback(function ($url) {
				return str_contains((string) $url, 'v1/content/articles/42');
			}))
			->willReturn(createJoomlaResponse(200, $readBody));

		$this->mockHttp
			->expects($this->once())
			->method('patch')
			->with(
				$this->callback(function ($url) {
					return str_contains((string) $url, 'v1/content/articles/42');
				}),
				$this->callback(function ($data) {
					$decoded = json_decode($data, true);
					return $decoded['title'] === 'Updated Title'
						&& $decoded['alias'] === 'existing-title'
						&& $decoded['catid'] === 2
						&& $decoded['images']['image_intro'] === 'images/new-intro.jpg'
						&& $decoded['images']['image_fulltext'] === 'images/old-full.jpg'
						&& $decoded['metadata']['author'] === 'Existing Meta Author'
						&& $decoded['introtext'] === '<p>Existing intro</p>'
						&& $decoded['fulltext'] === '<p>Existing full</p>';
				}),
				$this->anything(),
				$this->anything()
			)
			->willReturn(createJoomlaResponse(200, $updateBody));

		$this->articles->updateArticle(
			articleId: 42,
			title: 'Updated Title',
			imageIntro: 'images/new-intro.jpg',
		);
	}

	public function testUpdateArticleParsesJsonEncodedNestedFields(): void
	{
		$readBody = json_encode([
			'data' => [
				'type'       => 'articles',
				'id'         => '42',
				'attributes' => [
					'title'    => 'Existing Title',
					'alias'    => 'existing-title',
					'catid'    => 2,
					'images'   => json_encode([
						'image_intro'            => 'images/old-intro.jpg',
						'image_intro_alt'        => 'Old intro alt',
						'float_intro'            => 'left',
						'image_intro_caption'    => 'Old intro caption',
						'image_fulltext'         => 'images/old-full.jpg',
						'image_fulltext_alt'     => 'Old full alt',
						'float_fulltext'         => 'none',
						'image_fulltext_caption' => 'Old full caption',
					]),
					'urls'     => json_encode([
						'urla'     => 'https://a.example.com',
						'urlatext' => 'URL A',
						'targeta'  => '_blank',
					]),
					'metadata' => json_encode([
						'robots' => 'index, follow',
						'author' => 'Existing Meta Author',
						'rights' => 'All rights reserved',
					]),
				],
			],
		]);

		$updateBody = json_encode([
			'data' => [
				'type' => 'articles',
				'id'   => '42',
			],
		]);

		$this->mockHttp
			->expects($this->once())
			->method('get')
			->willReturn(createJoomlaResponse(200, $readBody));

		$this->mockHttp
			->expects($this->once())
			->method('patch')
			->with(
				$this->anything(),
				$this->callback(function ($data) {
					$decoded = json_decode($data, true);

					return $decoded['images']['image_intro'] === 'images/new-intro.jpg'
						&& $decoded['images']['image_fulltext'] === 'images/old-full.jpg'
						&& $decoded['urls']['urla'] === 'https://a.example.com'
						&& $decoded['metadata']['author'] === 'Existing Meta Author';
				}),
				$this->anything(),
				$this->anything()
			)
			->willReturn(createJoomlaResponse(200, $updateBody));

		$this->articles->updateArticle(
			articleId: 42,
			imageIntro: 'images/new-intro.jpg',
		);
	}

	public function testUpdateArticleAcceptsNestedImagesPayload(): void
	{
		$readBody = json_encode([
			'data' => [
				'type'       => 'articles',
				'id'         => '1',
				'attributes' => [
					'title'    => 'Existing Title',
					'alias'    => 'existing-title',
					'catid'    => 2,
					'images'   => [
						'image_intro' => 'images/old-intro.jpg',
					],
				],
			],
		]);

		$updateBody = json_encode([
			'data' => [
				'type' => 'articles',
				'id'   => '1',
			],
		]);

		$this->mockHttp
			->expects($this->once())
			->method('get')
			->willReturn(createJoomlaResponse(200, $readBody));

		$this->mockHttp
			->expects($this->once())
			->method('patch')
			->with(
				$this->anything(),
				$this->callback(function ($data) {
					$decoded = json_decode($data, true);

					return $decoded['catid'] === 8
						&& $decoded['images']['image_intro'] === 'https://cdn.pixabay.com/photo/2021/05/22/22/35/black-hole-6274731_1280.jpg';
				}),
				$this->anything(),
				$this->anything()
			)
			->willReturn(createJoomlaResponse(200, $updateBody));

		$this->articles->updateArticle(
			articleId: 1,
			catId: 8,
			images: [
				'image_intro' => 'https://cdn.pixabay.com/photo/2021/05/22/22/35/black-hole-6274731_1280.jpg',
			],
		);
	}

	public function testDeleteArticleReturnsTrue(): void
	{
		$trashBody = json_encode(['data' => ['type' => 'articles', 'id' => '42', 'attributes' => ['state' => -2]]]);

		$this->mockHttp
			->expects($this->once())
			->method('patch')
			->willReturn(createJoomlaResponse(200, $trashBody));

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
		$trashBody = json_encode(['data' => ['type' => 'articles', 'id' => '42', 'attributes' => ['state' => -2]]]);

		$this->mockHttp
			->expects($this->once())
			->method('patch')
			->willReturn(createJoomlaResponse(200, $trashBody));

		$this->mockHttp
			->expects($this->once())
			->method('delete')
			->willReturn(createJoomlaResponse(200, '{"data":{}}'));

		$result = $this->articles->deleteArticle(42);

		$this->assertFalse($result);
	}
}

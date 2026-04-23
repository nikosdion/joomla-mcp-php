<?php
/**
 * @package   joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license   AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Dionysopoulos\Mcp4Joomla\Tests\Unit\Server\Tickets;

use Dionysopoulos\Mcp4Joomla\Server\Tickets\Categories;
use Dionysopoulos\Mcp4Joomla\Tests\Unit\Stubs\TestContainerTrait;
use PHPUnit\Framework\TestCase;

class CategoriesTest extends TestCase
{
	use TestContainerTrait;

	private Categories $categories;

	protected function setUp(): void
	{
		$this->setUpTestContainer();
		$this->categories = new Categories();
	}

	protected function tearDown(): void
	{
		$this->tearDownTestContainer();
	}

	public function testListCategoriesNoFilters(): void
	{
		$body = json_encode([
			'data' => [
				['type' => 'categories', 'id' => '1', 'attributes' => ['title' => 'Support']],
			],
		]);

		$this->mockHttp
			->expects($this->once())
			->method('get')
			->with($this->callback(fn($url) => str_contains((string) $url, 'v1/ats/categories')))
			->willReturn(createJoomlaResponse(200, $body));

		$result = $this->categories->listCategories();

		$this->assertIsObject($result);
		$this->assertCount(1, $result->data);
		$this->assertSame('categories', $result->data[0]->type);
	}

	public function testListCategoriesWithFilters(): void
	{
		$body = json_encode(['data' => []]);

		$this->mockHttp
			->expects($this->once())
			->method('get')
			->with($this->callback(fn($url) => str_contains((string) $url, 'filter[search]=Support')))
			->willReturn(createJoomlaResponse(200, $body));

		$result = $this->categories->listCategories(filterSearch: 'Support', pageLimit: 5, pageOffset: 0);

		$this->assertIsObject($result);
		$this->assertIsArray($result->data);
	}

	public function testReadCategory(): void
	{
		$body = json_encode([
			'data' => ['type' => 'categories', 'id' => '3', 'attributes' => ['title' => 'General']],
		]);

		$this->mockHttp
			->expects($this->once())
			->method('get')
			->with($this->callback(fn($url) => str_contains((string) $url, 'v1/ats/categories/3')))
			->willReturn(createJoomlaResponse(200, $body));

		$result = $this->categories->readCategory(3);

		$this->assertIsObject($result);
		$this->assertSame('categories', $result->data->type);
	}
}

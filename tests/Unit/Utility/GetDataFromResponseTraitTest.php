<?php
/**
 * @package   joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license   AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Dionysopoulos\Mcp4Joomla\Tests\Unit\Utility;

use Dionysopoulos\Mcp4Joomla\Tests\Unit\Stubs\GetDataFromResponseStub;
use PHPUnit\Framework\TestCase;

class GetDataFromResponseTraitTest extends TestCase
{
	private GetDataFromResponseStub $stub;

	protected function setUp(): void
	{
		$this->stub = new GetDataFromResponseStub();
	}

	public function testSingleObjectData(): void
	{
		$body     = json_encode([
			'data' => [
				'type'       => 'articles',
				'id'         => '1',
				'attributes' => ['title' => 'Test'],
			],
		]);
		$response = createJoomlaResponse(200, $body);

		$result = $this->stub->getDataFromResponse($response, 'articles');

		$this->assertIsObject($result);
		$this->assertSame('articles', $result->data->type);
	}

	public function testArrayData(): void
	{
		$body     = json_encode([
			'data' => [
				[
					'type'       => 'articles',
					'id'         => '1',
					'attributes' => ['title' => 'One'],
				],
				[
					'type'       => 'articles',
					'id'         => '2',
					'attributes' => ['title' => 'Two'],
				],
			],
		]);
		$response = createJoomlaResponse(200, $body);

		$result = $this->stub->getDataFromResponse($response, 'articles');

		$this->assertIsObject($result);
		$this->assertIsArray($result->data);
		$this->assertCount(2, $result->data);
	}

	public function testTypeValidationForSingleObject(): void
	{
		$body     = json_encode([
			'data' => [
				'type' => 'tags',
				'id'   => '1',
			],
		]);
		$response = createJoomlaResponse(200, $body);

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('expected type');

		$this->stub->getDataFromResponse($response, 'articles');
	}

	public function testTypeValidationForArray(): void
	{
		$body     = json_encode([
			'data' => [
				['type' => 'tags', 'id' => '1'],
			],
		]);
		$response = createJoomlaResponse(200, $body);

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('expected type');

		$this->stub->getDataFromResponse($response, 'articles');
	}

	public function testNoExpectedTypeSkipsValidation(): void
	{
		$body     = json_encode([
			'data' => [
				'type' => 'anything',
				'id'   => '1',
			],
		]);
		$response = createJoomlaResponse(200, $body);

		$result = $this->stub->getDataFromResponse($response);

		$this->assertSame('anything', $result->data->type);
	}

	public function testMissingDataThrows(): void
	{
		$body     = json_encode(['meta' => 'something']);
		$response = createJoomlaResponse(200, $body);

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('no data');

		$this->stub->getDataFromResponse($response);
	}

	public function testInvalidJsonThrows(): void
	{
		$response = createJoomlaResponse(200, 'not-json');

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('invalid JSON');

		$this->stub->getDataFromResponse($response);
	}

	public function testErrorsInSuccessResponseThrows(): void
	{
		$body     = json_encode([
			'errors' => [
				['title' => 'Something went wrong', 'code' => 500],
			],
		]);
		$response = createJoomlaResponse(200, $body);

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Something went wrong');

		$this->stub->getDataFromResponse($response);
	}

	public function testInvalidDataStructureThrows(): void
	{
		$body     = json_encode(['data' => 'not-an-object-or-array']);
		$response = createJoomlaResponse(200, $body);

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('invalid data structure');

		$this->stub->getDataFromResponse($response);
	}

	public function testArrayWithNonObjectItemThrows(): void
	{
		$body     = json_encode(['data' => ['string-item']]);
		$response = createJoomlaResponse(200, $body);

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('invalid data structure');

		$this->stub->getDataFromResponse($response);
	}
}

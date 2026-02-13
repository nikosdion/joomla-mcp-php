<?php
/**
 * @package   joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license   AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Dionysopoulos\Mcp4Joomla\Tests\Unit\Utility;

use Dionysopoulos\Mcp4Joomla\Tests\Unit\Stubs\HandleJoomlaAPIErrorStub;
use PHPUnit\Framework\TestCase;

class HandleJoomlaAPIErrorTraitTest extends TestCase
{
	private HandleJoomlaAPIErrorStub $stub;

	protected function setUp(): void
	{
		$this->stub = new HandleJoomlaAPIErrorStub();
	}

	public function testPassesThroughOn200(): void
	{
		$response = createJoomlaResponse(200, '{"data":{}}');

		// Should not throw
		$this->stub->handlePossibleJoomlaAPIError($response);
		$this->assertTrue(true);
	}

	public function testPassesThroughOn201(): void
	{
		$response = createJoomlaResponse(201, '{"data":{}}');

		$this->stub->handlePossibleJoomlaAPIError($response);
		$this->assertTrue(true);
	}

	public function testPassesThroughOn204(): void
	{
		$response = createJoomlaResponse(204);

		$this->stub->handlePossibleJoomlaAPIError($response);
		$this->assertTrue(true);
	}

	public function testJsonApiErrorThrowsDescriptive(): void
	{
		$body = json_encode([
			'errors' => [
				['title' => 'Not Found', 'code' => 404],
			],
		]);

		$response = createJoomlaResponse(404, $body);

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Not Found');
		$this->expectExceptionCode(404);

		$this->stub->handlePossibleJoomlaAPIError($response);
	}

	public function testNonJsonErrorThrowsGeneric(): void
	{
		$response = createJoomlaResponse(500, 'Internal Server Error');

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Failed to process Joomla API response');
		$this->expectExceptionCode(500);

		$this->stub->handlePossibleJoomlaAPIError($response);
	}

	public function testMalformedJsonApiErrorThrowsGeneric(): void
	{
		$body = json_encode([
			'errors' => [
				['unexpected' => 'format'],
			],
		]);

		$response = createJoomlaResponse(400, $body);

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionCode(400);

		$this->stub->handlePossibleJoomlaAPIError($response);
	}

	public function testEmptyErrorsArrayThrowsGeneric(): void
	{
		$body     = json_encode(['errors' => []]);
		$response = createJoomlaResponse(400, $body);

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionCode(400);

		$this->stub->handlePossibleJoomlaAPIError($response);
	}
}

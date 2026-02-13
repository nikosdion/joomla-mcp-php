<?php
/**
 * @package   joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license   AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Dionysopoulos\Mcp4Joomla\Tests\Unit\Utility;

use Dionysopoulos\Mcp4Joomla\Tests\Unit\Stubs\VarToLogStub;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class VarToLogTraitTest extends TestCase
{
	private VarToLogStub $stub;

	protected function setUp(): void
	{
		$this->stub = new VarToLogStub();
	}

	public static function varToLogProvider(): iterable
	{
		yield 'string' => ['hello', '"hello"'];
		yield 'empty string' => ['', '""'];
		yield 'integer' => [42, '42'];
		yield 'zero' => [0, '0'];
		yield 'float' => [3.14, '3.14'];
		yield 'bool true' => [true, 'true'];
		yield 'bool false' => [false, 'false'];
		yield 'null' => [null, 'null'];
		yield 'simple array' => [['a', 'b'], '[0 => "a", 1 => "b"]'];
		yield 'assoc array' => [['key' => 'val'], '[key => "val"]'];
		yield 'nested array' => [['a' => ['b' => 1]], '[a => [b => 1]]'];
	}

	#[DataProvider('varToLogProvider')]
	public function testVarToLog(mixed $input, string $expected): void
	{
		$this->assertSame($expected, $this->stub->varToLog($input));
	}

	public function testObjectConversion(): void
	{
		$obj = new \stdClass();
		$this->assertSame('object(stdClass)', $this->stub->varToLog($obj));
	}
}

<?php
/**
 * @package   joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license   AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Dionysopoulos\Mcp4Joomla\Tests\Unit\Utility;

use Dionysopoulos\Mcp4Joomla\Tests\Unit\Stubs\TitleToAliasStub;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class TitleToAliasTraitTest extends TestCase
{
	private TitleToAliasStub $stub;

	protected function setUp(): void
	{
		$this->stub = new TitleToAliasStub();
	}

	public static function aliasProvider(): iterable
	{
		yield 'simple spaces' => ['Hello World', 'hello-world'];
		yield 'multiple spaces' => ['Hello   World', 'hello-world'];
		yield 'special chars' => ['Hello: World!', 'hello-world'];
		yield 'URL chars' => ['foo?bar=baz&qux', 'foo-bar-baz-qux'];
		yield 'leading/trailing spaces' => ['  Hello  ', 'hello'];
		yield 'dashes preserved' => ['hello-world', 'hello-world'];
		yield 'parentheses removed' => ['Hello (World)', 'hello-world'];
		yield 'dots removed' => ['file.name.ext', 'file-name-ext'];
		yield 'unicode preserved' => ['Héllo Wörld', 'héllo-wörld'];
		yield 'already lowercase' => ['hello', 'hello'];
	}

	#[DataProvider('aliasProvider')]
	public function testTitleToAlias(string $input, string $expected): void
	{
		$this->assertSame($expected, $this->stub->titleToAlias($input));
	}
}

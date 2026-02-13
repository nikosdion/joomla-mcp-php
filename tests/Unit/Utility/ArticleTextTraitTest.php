<?php
/**
 * @package   joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license   AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Dionysopoulos\Mcp4Joomla\Tests\Unit\Utility;

use Dionysopoulos\Mcp4Joomla\Tests\Unit\Stubs\ArticleTextStub;
use PHPUnit\Framework\TestCase;

class ArticleTextTraitTest extends TestCase
{
	private ArticleTextStub $stub;

	protected function setUp(): void
	{
		$this->stub = new ArticleTextStub();
	}

	public function testMarkdownToHtml(): void
	{
		$result = $this->stub->toHtml('**bold** text');
		$this->assertStringContainsString('<strong>bold</strong>', $result);
		$this->assertStringContainsString('text', $result);
	}

	public function testHeadingConversion(): void
	{
		$result = $this->stub->toHtml('# Heading');
		$this->assertStringContainsString('<h1>Heading</h1>', $result);
	}

	public function testNullReturnsEmpty(): void
	{
		$this->assertSame('', $this->stub->toHtml(null));
	}

	public function testEmptyStringReturnsEmpty(): void
	{
		$this->assertSame('', $this->stub->toHtml(''));
	}

	public function testHtmlPassthrough(): void
	{
		$html   = '<p>Already <strong>HTML</strong></p>';
		$result = $this->stub->toHtml($html);
		$this->assertStringContainsString('<strong>HTML</strong>', $result);
	}
}

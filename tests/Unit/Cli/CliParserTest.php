<?php
/**
 * @package   joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license   AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Dionysopoulos\Mcp4Joomla\Tests\Unit\Cli;

use Dionysopoulos\Mcp4Joomla\Cli\CliParser;
use PHPUnit\Framework\TestCase;

class CliParserTest extends TestCase
{
	// -------------------------------------------------------------------------
	// Helpers
	// -------------------------------------------------------------------------

	/**
	 * Parse a fake argv array by temporarily overriding the global $argv.
	 */
	private function parse(CliParser $parser, array $args): \Dionysopoulos\Mcp4Joomla\Cli\CliInput
	{
		global $argv;
		$saved = $argv;
		$argv  = array_merge(['script.php'], $args);
		$result = $parser->parse();
		$argv  = $saved;

		return $result;
	}

	private function makeParser(): CliParser
	{
		$parser = new CliParser('Test', 'Test parser');
		$parser
			->addFlag(['verbose', 'v'], 'Verbose output')
			->addParam(['log', 'l'], 'FILE', 'Log file')
			->addMultiParam(['forbidden'], 'SECRET', 'Forbidden secret value');

		return $parser;
	}

	// -------------------------------------------------------------------------
	// Flag tests
	// -------------------------------------------------------------------------

	public function testFlagDefaultIsFalse(): void
	{
		$input = $this->parse($this->makeParser(), []);

		$this->assertFalse($input->verbose);
	}

	public function testFlagIsSetByLongOption(): void
	{
		$input = $this->parse($this->makeParser(), ['--verbose']);

		$this->assertTrue($input->verbose);
	}

	public function testFlagIsSetByShortOption(): void
	{
		$input = $this->parse($this->makeParser(), ['-v']);

		$this->assertTrue($input->verbose);
	}

	// -------------------------------------------------------------------------
	// Regular param tests
	// -------------------------------------------------------------------------

	public function testParamDefaultIsNull(): void
	{
		$input = $this->parse($this->makeParser(), []);

		$this->assertNull($input->log);
	}

	public function testParamIsSetByLongEqualsSyntax(): void
	{
		$input = $this->parse($this->makeParser(), ['--log=/var/log/foo.log']);

		$this->assertSame('/var/log/foo.log', $input->log);
	}

	public function testParamIsSetByLongSpaceSyntax(): void
	{
		$input = $this->parse($this->makeParser(), ['--log', '/var/log/foo.log']);

		$this->assertSame('/var/log/foo.log', $input->log);
	}

	public function testParamIsSetByShortSyntax(): void
	{
		$input = $this->parse($this->makeParser(), ['-l', '/var/log/foo.log']);

		$this->assertSame('/var/log/foo.log', $input->log);
	}

	// -------------------------------------------------------------------------
	// Multi-param tests
	// -------------------------------------------------------------------------

	public function testMultiParamDefaultIsEmptyArray(): void
	{
		$input = $this->parse($this->makeParser(), []);

		$this->assertSame([], $input->forbidden);
	}

	public function testMultiParamCollectsSingleValueViaEqualsSyntax(): void
	{
		$input = $this->parse($this->makeParser(), ['--forbidden=secret1']);

		$this->assertSame(['secret1'], $input->forbidden);
	}

	public function testMultiParamCollectsSingleValueViaSpaceSyntax(): void
	{
		$input = $this->parse($this->makeParser(), ['--forbidden', 'secret1']);

		$this->assertSame(['secret1'], $input->forbidden);
	}

	public function testMultiParamAccumulatesMultipleValues(): void
	{
		$input = $this->parse($this->makeParser(), [
			'--forbidden=alpha',
			'--forbidden=beta',
			'--forbidden=gamma',
		]);

		$this->assertSame(['alpha', 'beta', 'gamma'], $input->forbidden);
	}

	public function testMultiParamMixedSyntaxAccumulates(): void
	{
		$input = $this->parse($this->makeParser(), [
			'--forbidden=alpha',
			'--forbidden', 'beta',
		]);

		$this->assertSame(['alpha', 'beta'], $input->forbidden);
	}

	public function testMultiParamDoesNotAffectOtherParams(): void
	{
		$input = $this->parse($this->makeParser(), [
			'--forbidden=secret',
			'--log=/tmp/out.log',
			'--verbose',
		]);

		$this->assertSame(['secret'], $input->forbidden);
		$this->assertSame('/tmp/out.log', $input->log);
		$this->assertTrue($input->verbose);
	}
}

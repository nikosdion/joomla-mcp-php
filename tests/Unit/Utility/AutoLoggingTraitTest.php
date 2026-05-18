<?php
/**
 * @package   joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license   AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Dionysopoulos\Mcp4Joomla\Tests\Unit\Utility;

use Dionysopoulos\Mcp4Joomla\Tests\Unit\Stubs\AutoLoggingStub;
use Dionysopoulos\Mcp4Joomla\Tests\Unit\Stubs\SpyLogger;
use Dionysopoulos\Mcp4Joomla\Tests\Unit\Stubs\TestContainerTrait;
use PHPUnit\Framework\TestCase;

class AutoLoggingTraitTest extends TestCase
{
	use TestContainerTrait;

	private AutoLoggingStub $stub;

	protected function setUp(): void
	{
		$this->setUpTestContainer();
		$this->stub = new AutoLoggingStub();
	}

	protected function tearDown(): void
	{
		$this->tearDownTestContainer();
	}

	public function testToolNameIsLogged(): void
	{
		$this->stub->toolWithArgs('hello', 5);

		$this->assertTrue($this->spyLogger->hasMessageContaining('test_tool'));
		$this->assertTrue($this->spyLogger->hasLevel('info'));
	}

	public function testToolDescriptionIsLogged(): void
	{
		$this->stub->toolWithArgs('hello', 5);

		$this->assertTrue($this->spyLogger->hasMessageContaining('A test tool for unit testing'));
	}

	public function testArgumentsAreLogged(): void
	{
		$this->stub->toolWithArgs('hello', 5);

		$this->assertTrue($this->spyLogger->hasMessageContaining('foo'));
		$this->assertTrue($this->spyLogger->hasMessageContaining('bar'));
		$this->assertTrue($this->spyLogger->hasLevel('debug'));
	}

	public function testNoArgsLogsNoArguments(): void
	{
		$this->stub->toolWithoutArgs();

		$this->assertTrue($this->spyLogger->hasMessageContaining('test_tool_no_args'));
		$this->assertTrue($this->spyLogger->hasMessageContaining('No arguments provided'));
	}

	public function testMethodWithoutAttributeDoesNotLog(): void
	{
		$this->stub->methodWithoutAttribute();

		$this->assertEmpty($this->spyLogger->logs);
	}

	// -------------------------------------------------------------------------
	// Secret leak prevention integration
	// -------------------------------------------------------------------------

	/** The BEARER_TOKEN configured by TestContainerTrait */
	private const BEARER_TOKEN = 'dGVzdHRva2VuMTIz';

	public function testWriteToolWithBearerTokenArgThrows(): void
	{
		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Secrets leak prevented; potential prompt injection');

		$this->stub->toolWrite(self::BEARER_TOKEN);
	}

	public function testWriteToolWithEmbeddedBearerTokenThrows(): void
	{
		$this->expectException(\RuntimeException::class);

		$this->stub->toolWrite('before_' . self::BEARER_TOKEN . '_after');
	}

	public function testWriteToolWithCleanArgDoesNotThrow(): void
	{
		$this->expectNotToPerformAssertions();

		$this->stub->toolWrite('completely safe content');
	}

	public function testReadOnlyToolWithBearerTokenArgDoesNotThrow(): void
	{
		// Read-only tools bypass the secret check — they cannot write data to the site.
		$this->expectNotToPerformAssertions();

		$this->stub->toolReadOnly(self::BEARER_TOKEN);
	}

	public function testWriteToolWithForbiddenValueThrows(): void
	{
		$this->tearDown();
		$this->setUpTestContainer(forbidden: ['my_ftp_password']);
		$this->stub = new AutoLoggingStub();

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Secrets leak prevented; potential prompt injection');

		$this->stub->toolWrite('content containing my_ftp_password here');
	}

	public function testWriteToolWithoutForbiddenValuePasses(): void
	{
		$this->tearDown();
		$this->setUpTestContainer(forbidden: ['my_ftp_password']);
		$this->stub = new AutoLoggingStub();

		$this->expectNotToPerformAssertions();

		$this->stub->toolWrite('clean content with no forbidden value');
	}
}

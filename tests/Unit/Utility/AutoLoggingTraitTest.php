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
}

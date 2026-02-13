<?php
/**
 * @package   joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license   AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Dionysopoulos\Mcp4Joomla\Tests\Unit\Stubs;

use Dionysopoulos\Mcp4Joomla\Utility\AutoLoggingTrait;
use Dionysopoulos\Mcp4Joomla\Utility\VarToLogTrait;
use PhpMcp\Server\Attributes\McpTool;

class AutoLoggingStub
{
	use AutoLoggingTrait;
	use VarToLogTrait;

	#[McpTool(
		name: 'test_tool',
		description: 'A test tool for unit testing'
	)]
	public function toolWithArgs(string $foo, int $bar = 42): void
	{
		$this->autologMCPTool();
	}

	#[McpTool(
		name: 'test_tool_no_args',
		description: 'A test tool with no arguments'
	)]
	public function toolWithoutArgs(): void
	{
		$this->autologMCPTool();
	}

	public function methodWithoutAttribute(): void
	{
		$this->autologMCPTool();
	}
}

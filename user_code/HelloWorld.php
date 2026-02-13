<?php
/**
 * Demo user code tool for MCP4Joomla.
 *
 * This file demonstrates how to create custom MCP tools. You can safely delete it.
 * Drop any PHP file with #[McpTool] attributes in this directory and it will be
 * auto-discovered alongside the built-in tools.
 *
 * @package   joomla-mcp-php
 * @license   AGPL-3.0-or-later
 */

declare(strict_types=1);

use Dionysopoulos\Mcp4Joomla\Container\Factory;
use Dionysopoulos\Mcp4Joomla\Utility\AutoLoggingTrait;
use PhpMcp\Schema\ToolAnnotations;
use PhpMcp\Server\Attributes\McpTool;

class HelloWorld
{
	use AutoLoggingTrait;

	#[McpTool(
		name: 'user_hello_world',
		description: 'A demo user code tool that returns a greeting. You can safely delete this file.',
		annotations: new ToolAnnotations(
			readOnlyHint: true,
			destructiveHint: false,
			idempotentHint: true,
		),
	)]
	public function greet(
		string $name = 'World',
	): string
	{
		$this->autologMCPTool();

		/** @var \Psr\Log\LoggerInterface $log */
		$log = Factory::getContainer()->get('log');
		$log->debug("HelloWorld tool called with name: {$name}");

		return "Hello, {$name}! This is a demo tool from the user_code directory.";
	}
}

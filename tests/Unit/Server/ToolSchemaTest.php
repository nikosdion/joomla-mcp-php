<?php
/**
 * @package   joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license   AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Dionysopoulos\Mcp4Joomla\Tests\Unit\Server;

use PhpMcp\Server\Utils\DocBlockParser;
use PhpMcp\Server\Utils\SchemaGenerator;
use PhpMcp\Server\Utils\SchemaValidator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;

/**
 * Checks the JSON Schema that php-mcp/server generates for our MCP tools, which is what both the MCP client and the
 * server's own Dispatcher validate tool arguments against.
 */
class ToolSchemaTest extends TestCase
{
	/**
	 * Every PHP file under src/Server, as [relative path => [absolute path]].
	 */
	public static function toolSourceFilesProvider(): array
	{
		$srcRoot  = dirname(__DIR__, 3) . '/src/Server';
		$files    = [];
		$iterator = new \RecursiveIteratorIterator(
			new \RecursiveDirectoryIterator($srcRoot, \FilesystemIterator::SKIP_DOTS)
		);

		foreach ($iterator as $file)
		{
			if ($file->getExtension() === 'php')
			{
				$files[substr($file->getPathname(), strlen($srcRoot) + 1)] = [$file->getPathname()];
			}
		}

		ksort($files);

		return $files;
	}

	public static function menuItemRequestProvider(): array
	{
		return [
			'site create'  => ['Dionysopoulos\\Mcp4Joomla\\Server\\Menus\\MenuItems', 'createMenuItem'],
			'site update'  => ['Dionysopoulos\\Mcp4Joomla\\Server\\Menus\\MenuItems', 'updateMenuItem'],
			'admin create' => ['Dionysopoulos\\Mcp4Joomla\\Server\\Menus\\AdminMenuItems', 'createMenuItem'],
			'admin update' => ['Dionysopoulos\\Mcp4Joomla\\Server\\Menus\\AdminMenuItems', 'updateMenuItem'],
		];
	}

	/**
	 * No optional parameter may be declared before a required one.
	 *
	 * PHP silently treats such a parameter as required and drops its default value, so php-mcp/server lists it in
	 * the tool schema's "required" array and callers are forced to supply it. Reflection can't tell these apart from
	 * genuinely required parameters, so we look for the compile-time deprecation PHP emits instead.
	 */
	#[DataProvider('toolSourceFilesProvider')]
	public function testNoOptionalParameterBeforeRequired(string $file): void
	{
		$command = sprintf(
			'%s -d error_reporting=-1 -d display_errors=stdout -l %s 2>&1',
			escapeshellarg(PHP_BINARY),
			escapeshellarg($file)
		);

		exec($command, $output, $exitCode);

		$output = implode("\n", $output);

		$this->assertSame(0, $exitCode, $output);
		$this->assertStringNotContainsString('implicitly treated as a required parameter', $output);
	}

	/**
	 * The menu item "request" parameter is a key/value map, so it must accept a JSON object.
	 */
	#[DataProvider('menuItemRequestProvider')]
	public function testMenuItemRequestAcceptsObject(string $class, string $method): void
	{
		$schema    = (new SchemaGenerator(new DocBlockParser()))->generate(new \ReflectionMethod($class, $method));
		$validator = new SchemaValidator(new NullLogger());
		$errors    = $validator->validateAgainstJsonSchema(
			json_decode('{"option": "com_content", "view": "category", "id": "14"}'),
			$schema['properties']['request']
		);

		$this->assertSame([], $errors);
	}
}

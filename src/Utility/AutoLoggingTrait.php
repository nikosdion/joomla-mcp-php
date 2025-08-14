<?php
/**
 * @package       joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license       AGPL-3.0-or-later
 */

namespace Dionysopoulos\Mcp4Joomla\Utility;

use Dionysopoulos\Mcp4Joomla\Container\Factory;
use PhpMcp\Server\Attributes\McpTool;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;
use ReflectionFunction;
use ReflectionMethod;

/**
 * A Trait to allow MCP Element classes to semi-automatically log calls to MCP tools.
 */
trait AutoLoggingTrait
{
	/**
	 * Returns an associative array of named arguments for the calling function or method.
	 *
	 * Retrieves named arguments for the calling function or method by combining the parameter names
	 * with their corresponding values from the argument list. Supports adding default values
	 * when parameters are omitted and optionally handling variadic arguments.
	 *
	 * @param   int   $callDepth     The stack-depth at which to inspect the call, default is 2.
	 * @param   bool  $withDefaults  Whether to include default values for omitted parameters, default is false.
	 *
	 * @return  array An associative array mapping parameter names to their respective values,
	 *               including default or null if not explicitly provided when applicable.
	 * @throws  \ReflectionException  When all Hell breaks loose…
	 */
	private function methodArgsWithNames(int $callDepth = 1, bool $withDefaults = false): array
	{
		// Figure out *where* we were called from
		$trace    = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, $callDepth + 2)[$callDepth + 1];
		$args     = $trace['args'] ?? [];          // argument values
		$isMethod = isset($trace['class']);        // method or function?

		// Use Reflection to get the parameter list
		$ref = $isMethod
			? new ReflectionMethod($trace['class'], $trace['function'])
			: new ReflectionFunction($trace['function']);

		$params = $ref->getParameters();

		// Combine parameter names and values
		$named = [];

		foreach ($params as $position => $param)
		{
			$name = $param->getName();

			// Do we have a concrete value for this parameter?
			if (array_key_exists($position, $args))
			{
				$named[$name] = $args[$position];

				continue;
			}
			// The parameter was omitted. Do we have a default value?
			elseif ($param->isDefaultValueAvailable())
			{
				// By default, we don't emmit omitted parameters with default values.
				if ($withDefaults)
				{
					$named[$name] = $param->getDefaultValue();
				}
			}
			else
			{
				// Omitted *and* no default is treated as null (though PHP may have already thrown an error by this time).
				$named[$name] = null;
			}

			// Handle variadic parameters
			if ($param->isVariadic())
			{
				$extra = array_slice($args, $position);

				foreach ($extra as $k => $v)
				{
					$named[sprintf("%s[%s]", $name, $k)] = $v;
				}

				// A variadic parameter is always the last parameter (on pain of parser error)
				break;
			}
		}

		return $named;
	}

	/**
	 * Automatically logs the invocation of an MCP Tool annotated method or function at an info level.
	 *
	 * The log includes its arguments (names and values) at a debug level.
	 *
	 * @return  void
	 * @throws  ContainerExceptionInterface
	 * @throws  NotFoundExceptionInterface
	 * @throws  \ReflectionException
	 */
	private function autologMCPTool(): void
	{
		/** @var LoggerInterface $logger */
		$logger   = Factory::getContainer()->get('log');
		$trace    = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1];
		$isMethod = isset($trace['class']);        // method or function?

		// Use Reflection to get the parameter list
		$ref = $isMethod
			? new ReflectionMethod($trace['class'], $trace['function'])
			: new ReflectionFunction($trace['function']);

		$attributes = $ref->getAttributes();

		/** @var \ReflectionAttribute|null $mcpToolAttribute */
		$mcpToolAttribute = null;

		foreach ($attributes as $attribute)
		{
			if ($attribute->getName() === McpTool::class)
			{
				$mcpToolAttribute = $attribute;
				break;
			}
		}

		if ($mcpToolAttribute === null)
		{
			return;
		}

		$mcpToolInstance = $mcpToolAttribute->newInstance();
		$name            = $mcpToolInstance->name;
		$description     = $mcpToolInstance->description;

		if (empty($name))
		{
			return;
		}

		$message = sprintf('MCP Tool call: %s', $name);
		$message .= $description ? " - [$description]" : '';

		$logger->info($message);

		$arguments = $this->methodArgsWithNames();

		if (empty($arguments))
		{
			$logger->debug('No arguments provided');

			return;
		}

		$logger->debug('Arguments:', $arguments);

		foreach ($arguments as $k => $v)
		{
			$logger->debug(sprintf('  %s: %s', $k, $this->varToLog($v)));
		}
	}
}
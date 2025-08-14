<?php
/**
 * @package   joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license   AGPL-3.0-or-later
 */

namespace Dionysopoulos\Mcp4Joomla\Utility;

trait VarToLogTrait
{
	/**
	 * Converts a value into a string representation suitable for logging.
	 *
	 * @param   mixed  $var  The variable to convert
	 *
	 * @return  string  The string representation of the variable
	 */
	private function varToLog(mixed $var): string
	{
		if (is_array($var))
		{
			$output = [];

			foreach ($var as $k => $v)
			{
				$output[] = sprintf('%s => %s', $k, $this->varToLog($v));
			}

			return sprintf('[%s]', implode(', ', $output));
		}

		if (is_bool($var))
		{
			return $var ? 'true' : 'false';
		}

		if (is_null($var))
		{
			return 'null';
		}

		if (is_string($var))
		{
			return sprintf('"%s"', $var);
		}

		if (is_object($var))
		{
			return sprintf('object(%s)', get_class($var));
		}

		if (!is_scalar($var))
		{
			return 'Non-scalar value (' . gettype($var) . ')';
		}

		return (string) $var;
	}
}
<?php
/**
 * @package   joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license   AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Dionysopoulos\Mcp4Joomla\Cli;

/**
 * Holds parsed CLI input values, with camelCase property access.
 *
 * Properties are accessed by the camelCase version of the option's long name,
 * e.g. --no-panopticon becomes $input->noPanopticon.
 */
class CliInput
{
	public function __construct(private readonly array $values)
	{
	}

	public function __get(string $name): mixed
	{
		return $this->values[$name] ?? null;
	}

	public function __isset(string $name): bool
	{
		return isset($this->values[$name]);
	}
}

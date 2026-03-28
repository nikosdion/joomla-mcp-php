<?php
/**
 * @package   joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license   AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Dionysopoulos\Mcp4Joomla\Cli;

/**
 * Minimal CLI argument parser, replacing the douglasgreen/opt-parser dependency.
 *
 * Supports long options (--flag, --param=value, --param value) and short aliases
 * (-f, -p value). Option names are converted from kebab-case to camelCase on the
 * returned CliInput object.
 */
class CliParser
{
	/** @var array<string, string> Long flag name → description */
	private array $flags = [];

	/** @var array<string, string> Long param name → description */
	private array $params = [];

	/** @var array<string, string> Short alias → long flag name */
	private array $shortFlags = [];

	/** @var array<string, string> Short alias → long param name */
	private array $shortParams = [];

	public function __construct(private readonly string $name, private readonly string $description)
	{
	}

	public function addCommand(array $names, string $description): static
	{
		return $this;
	}

	public function addFlag(array $names, string $description): static
	{
		$long = $names[0];
		$this->flags[$long] = $description;

		for ($i = 1; $i < count($names); $i++)
		{
			$this->shortFlags[$names[$i]] = $long;
		}

		return $this;
	}

	public function addParam(array $names, string $type, string $description): static
	{
		$long = $names[0];
		$this->params[$long] = $description;

		for ($i = 1; $i < count($names); $i++)
		{
			$this->shortParams[$names[$i]] = $long;
		}

		return $this;
	}

	public function addUsage(string $command, array $options): static
	{
		return $this;
	}

	public function parse(): CliInput
	{
		global $argv;
		$args = array_slice($argv ?? [], 1);

		$result = [];

		foreach (array_keys($this->flags) as $long)
		{
			$result[$this->toCamelCase($long)] = false;
		}

		foreach (array_keys($this->params) as $long)
		{
			$result[$this->toCamelCase($long)] = null;
		}

		$count = count($args);

		for ($i = 0; $i < $count; $i++)
		{
			$arg = $args[$i];

			if (str_starts_with($arg, '--'))
			{
				$longArg = substr($arg, 2);

				if (str_contains($longArg, '='))
				{
					[$name, $value] = explode('=', $longArg, 2);

					if (isset($this->params[$name]))
					{
						$result[$this->toCamelCase($name)] = $value;
					}
				}
				elseif (isset($this->flags[$longArg]))
				{
					$result[$this->toCamelCase($longArg)] = true;
				}
				elseif (isset($this->params[$longArg]) && $i + 1 < $count && !str_starts_with($args[$i + 1], '-'))
				{
					$result[$this->toCamelCase($longArg)] = $args[++$i];
				}
			}
			elseif (str_starts_with($arg, '-') && strlen($arg) === 2)
			{
				$short = $arg[1];

				if (isset($this->shortFlags[$short]))
				{
					$result[$this->toCamelCase($this->shortFlags[$short])] = true;
				}
				elseif (isset($this->shortParams[$short]) && $i + 1 < $count && !str_starts_with($args[$i + 1], '-'))
				{
					$result[$this->toCamelCase($this->shortParams[$short])] = $args[++$i];
				}
			}
		}

		return new CliInput($result);
	}

	private function toCamelCase(string $name): string
	{
		return lcfirst(str_replace(' ', '', ucwords(str_replace('-', ' ', $name))));
	}
}

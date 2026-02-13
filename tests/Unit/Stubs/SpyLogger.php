<?php
/**
 * @package   joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license   AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Dionysopoulos\Mcp4Joomla\Tests\Unit\Stubs;

use Psr\Log\AbstractLogger;

/**
 * A logger that records all calls in a public array for test assertions.
 */
class SpyLogger extends AbstractLogger
{
	/** @var array<int, array{level: string, message: string, context: array}> */
	public array $logs = [];

	public function log($level, \Stringable|string $message, array $context = []): void
	{
		$this->logs[] = [
			'level'   => (string) $level,
			'message' => (string) $message,
			'context' => $context,
		];
	}

	/**
	 * Returns true if any logged message contains the given substring.
	 */
	public function hasMessageContaining(string $needle): bool
	{
		foreach ($this->logs as $entry)
		{
			if (str_contains($entry['message'], $needle))
			{
				return true;
			}
		}

		return false;
	}

	/**
	 * Returns true if any log entry has the given level.
	 */
	public function hasLevel(string $level): bool
	{
		foreach ($this->logs as $entry)
		{
			if ($entry['level'] === $level)
			{
				return true;
			}
		}

		return false;
	}
}

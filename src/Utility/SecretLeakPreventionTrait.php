<?php
/**
 * @package       joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license       AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Dionysopoulos\Mcp4Joomla\Utility;

use Dionysopoulos\Mcp4Joomla\Container\Factory;

// ============================================================
// SECRET LEAK PREVENTION
//
// This trait guards non-read-only MCP tools against prompt
// injection attacks that attempt to exfiltrate the BEARER_TOKEN
// by embedding it in tool parameters sent to the Joomla API.
//
// HOW IT WORKS
//   assertNoSecretLeak() receives the named arguments of a tool
//   method, recursively inspects every string value, and throws
//   a RuntimeException before the HTTP request is made if the
//   BEARER_TOKEN is detected anywhere in the input.
//
// EXTENDING
//   To add further secret detection (e.g. SSH keys, API tokens)
//   override or extend assertNoSecretLeak() or add additional
//   secrets to the $secrets array inside it.
// ============================================================

trait SecretLeakPreventionTrait
{
	/**
	 * Asserts that no tool argument contains a known application secret.
	 *
	 * Intended to be called for every MCP tool method that would be removed
	 * by the --non-destructive flag (i.e. methods not annotated with
	 * readOnlyHint: true).  The check is performed before the HTTP request
	 * is dispatched to the Joomla API, so a positive match aborts the call
	 * entirely.
	 *
	 * @param   array  $namedArgs  Associative array of parameter name → value
	 *                             for the tool method being invoked.
	 *
	 * @return  void
	 * @throws  \RuntimeException  When a secret is found in the arguments.
	 */
	private function assertNoSecretLeak(array $namedArgs): void
	{
		$secrets = $this->getProtectedSecrets();

		foreach ($secrets as $secret)
		{
			if ($this->argumentsContainSecret($namedArgs, $secret))
			{
				throw new \RuntimeException('Secrets leak prevented; potential prompt injection');
			}
		}
	}

	/**
	 * Returns the list of secrets that must never appear in tool arguments.
	 *
	 * Includes the BEARER_TOKEN automatically plus any values passed via the
	 * --forbidden command-line option.  Override or extend this method to add
	 * further secrets programmatically.
	 *
	 * @return  string[]  Non-empty secret strings to protect.
	 */
	private function getProtectedSecrets(): array
	{
		$container   = Factory::getContainer();
		$bearerToken = $container->get('env')['BEARER_TOKEN'] ?? null;
		$forbidden   = $container->get('input')->forbidden ?? [];

		if (!is_array($forbidden))
		{
			$forbidden = [$forbidden];
		}

		return array_values(
			array_filter(
				array_merge([$bearerToken], $forbidden),
				fn($s) => is_string($s) && $s !== ''
			)
		);
	}

	/**
	 * Recursively checks whether any string in the value tree contains the secret.
	 *
	 * Traverses nested arrays and checks every string leaf node.  Non-string,
	 * non-array values (int, bool, null) are skipped because they cannot carry
	 * a base64-encoded token.
	 *
	 * @param   mixed   $value   The value to inspect (string, array, or scalar).
	 * @param   string  $secret  The secret to search for.
	 *
	 * @return  bool  True when the secret was found inside $value.
	 */
	private function argumentsContainSecret(mixed $value, string $secret): bool
	{
		if (is_string($value))
		{
			return str_contains($value, $secret);
		}

		if (is_array($value))
		{
			foreach ($value as $item)
			{
				if ($this->argumentsContainSecret($item, $secret))
				{
					return true;
				}
			}
		}

		return false;
	}
}

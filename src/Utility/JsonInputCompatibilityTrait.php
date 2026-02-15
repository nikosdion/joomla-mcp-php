<?php
/**
 * @package   joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license   AGPL-3.0-or-later
 */

namespace Dionysopoulos\Mcp4Joomla\Utility;

trait JsonInputCompatibilityTrait
{
	/**
	 * Accepts either a JSON string or a structured value.
	 *
	 * If a JSON string is provided, it is decoded. Invalid JSON is returned unchanged.
	 */
	private function normaliseJsonCompatibleInput(mixed $value): mixed
	{
		if (!is_string($value))
		{
			return $value;
		}

		$trimmed = trim($value);

		if ($trimmed === '')
		{
			return $value;
		}

		try
		{
			return json_decode($value, true, flags: JSON_THROW_ON_ERROR);
		}
		catch (\JsonException)
		{
			return $value;
		}
	}

	/**
	 * Accepts either an associative array or a JSON object string and returns an associative array.
	 */
	private function normaliseJsonObjectInput(array|string $value, string $fieldName): array
	{
		if (is_array($value))
		{
			return $value;
		}

		try
		{
			$decoded = json_decode($value, true, flags: JSON_THROW_ON_ERROR);
		}
		catch (\JsonException)
		{
			throw new \RuntimeException(sprintf('The %s value must be a valid JSON object.', $fieldName));
		}

		if (!is_array($decoded))
		{
			throw new \RuntimeException(sprintf('The %s value must be a JSON object.', $fieldName));
		}

		return $decoded;
	}
}

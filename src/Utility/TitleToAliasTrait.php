<?php
/**
 * @package   joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license   AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Dionysopoulos\Mcp4Joomla\Utility;

trait TitleToAliasTrait
{
	/**
	 * Converts a given string into a URL-friendly Unicode slug by performing several transformations.
	 *
	 * @param   string  $string  The input string to be transformed into a URL slug.
	 *
	 * @return  string The resulting URL-friendly Unicode slug.
	 */
	private function titleToAlias(string $string): string
	{
		// Convert all Unicode whitespace and dashes to single byte spaces
		$str = preg_replace('/(\s|-)/u', "\x20", $string);

		// Replace characters with special URL meaning with spaces
		$str = preg_replace('#[:\#\*\?"@+=;!><&\.%()\]\/\'\\|\[]#', "\x20", $str);

		// Trim leading/trailing whitespace, and convert to lowercase
		$str = trim(function_exists('mb_strtolower') ? mb_strtolower($str, 'UTF-8') : strtolower($str));

		// Replace contiguous whitespace sequences with single dashes
		$str = preg_replace('#\x20+#', '-', $str);

		return $str;
	}

}
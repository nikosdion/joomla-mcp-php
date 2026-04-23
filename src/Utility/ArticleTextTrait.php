<?php
/**
 * @package   joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license   AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Dionysopoulos\Mcp4Joomla\Utility;

use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Exception\CommonMarkException;

trait ArticleTextTrait
{
	/**
	 * Converts the provided plain text into HTML format using the CommonMarkConverter.
	 *
	 * @param   string|null  $text  The input text to be converted into HTML.
	 *
	 * @return  string  The converted HTML string, or an empty string if the input text is empty.
	 * @throws CommonMarkException
	 */
	private function toHtml(?string $text = null): string
	{
		$converter = new CommonMarkConverter(
			[
				'html_input'         => 'allow',
				'allow_unsafe_links' => true,
			]
		);

		return empty($text) ? '' : $converter->convert($text)->getContent();
	}
}
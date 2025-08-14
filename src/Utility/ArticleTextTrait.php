<?php
/**
 * @package   joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license   AGPL-3.0-or-later
 */

namespace Dionysopoulos\Mcp4Joomla\Utility;

use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Exception\CommonMarkException;

trait ArticleTextTrait
{
	/**
	 * Converts and combines the introductory text and full text into a single article content.
	 *
	 * @param   string  $introText  The introductory text of the article.
	 * @param   string  $fullText   The full text of the article.
	 *
	 * @return  string The combined article content, with a delimiter if both parts are present.
	 * @throws  CommonMarkException
	 */
	private function getArticleText(?string $introText, ?string $fullText): string
	{
		$introText = trim($introText ?? '');
		$fullText  = trim($fullText ?? '');

		$converter = new CommonMarkConverter(
			[
				'html_input'         => 'allow',
				'allow_unsafe_links' => true,
			]
		);

		$introText = empty($introText) ? '' : $converter->convert($introText)->getContent();
		$fullText  = empty($fullText) ? '' : $converter->convert($fullText)->getContent();

		return $fullText ? ($introText . '<hr id="system-readmore">' . $fullText) : $introText;
	}

}
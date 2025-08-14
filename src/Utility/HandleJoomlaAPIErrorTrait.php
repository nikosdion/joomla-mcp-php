<?php
/**
 * @package   joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license   AGPL-3.0-or-later
 */

namespace Dionysopoulos\Mcp4Joomla\Utility;

use Joomla\Http\Response;

trait HandleJoomlaAPIErrorTrait
{
	public function handlePossibleJoomlaAPIError(Response $response): void
	{
		$statusCode = $response->getStatusCode();

		// We only handle HTTP statuses outside the 2xx range.
		if ($statusCode >= 200 && $statusCode < 300)
		{
			return;
		}

		// Try to parse the response body as JSON.
		$body = $response->getBody();

		try
		{
			$parsedBody = json_decode($body, false, flags: JSON_THROW_ON_ERROR);
		}
		catch (\JsonException $e)
		{
			$parsedBody = null;
		}

		// If we had a Joomla API error, throw a more descriptive exception.
		if ($parsedBody && isset($parsedBody->errors) && is_array($parsedBody->errors))
		{
			$error = array_shift($parsedBody->errors);

			if (is_object($error) && isset($error->title) && isset($error->code))
			{
				throw new \RuntimeException($error->title, $error->code);
			}
		}

		// Otherwise, throw a generic exception.
		throw new \RuntimeException('Failed to process Joomla API response: ' . $body, $statusCode);
	}
}
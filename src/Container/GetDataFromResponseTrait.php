<?php
/**
 * @package       joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license       AGPL-3.0-or-later
 */

namespace Dionysopoulos\Mcp4Joomla\Container;

use Joomla\Http\Response;

trait GetDataFromResponseTrait
{
	/**
	 * Processes the API response and extracts the data attributes if present.
	 *
	 * Throws an exception if the response contains errors, invalid JSON, or missing/incorrect data.
	 *
	 * @param   Response     $response      The response object containing the API response body.
	 * @param   string|null  $expectedType  The expected data type to validate against, if specified.
	 *
	 * @return  mixed  The data attributes from the response, or an array of data attributes if the response contains multiple items.
	 *
	 * @throws \RuntimeException If the API response contains errors or does not meet the expected structure or type.
	 */
	private function getDataFromResponse(Response $response, ?string $expectedType = null): mixed
	{
		$body = $response->getBody();

		try
		{
			$parsedBody = json_decode($body, false, flags: JSON_THROW_ON_ERROR);
		}
		catch (\JsonException)
		{
			$parsedBody = null;
		}

		/**
		 * Do we have a Joomla API error, despite the HTTP 2xx response?
		 *
		 * This should never happen, but one thing I've learned after 20 years of working with Joomla is that you can
		 * never be too careful with your error handling.
		 */
		if ($parsedBody && isset($parsedBody->errors) && is_array($parsedBody->errors) && !empty($parsedBody->errors))
		{
			$error = array_shift($parsedBody->errors);

			if (is_object($error) && isset($error->title) && isset($error->code))
			{
				throw new \RuntimeException($error->title, $error->code);
			}
		}

		if (!is_object($parsedBody))
		{
			throw new \RuntimeException('Failed to process Joomla API response (invalid JSON): ' . $body);
		}

		if (!isset($parsedBody->data))
		{
			throw new \RuntimeException('Failed to process Joomla API response (no data): ' . $body);
		}

		if (is_object($parsedBody->data))
		{
			$dataType = $parsedBody->data->type ?? 'invalid';

			if ($expectedType && $dataType !== $expectedType)
			{
				throw new \RuntimeException(
					sprintf(
						"Failed to process Joomla API response (expected type “%s”, got “%s”): %s", $expectedType,
						$dataType,
						$body
					)
				);
			}

			return $parsedBody->data;
		}

		if (is_array($parsedBody->data))
		{
			foreach ($parsedBody->data as $item)
			{
				if (!is_object($item))
				{
					throw new \RuntimeException('Failed to process Joomla API response (invalid data structure): ' . $body);
				}

				$dataType = $item->type ?? 'invalid';

				if ($expectedType && $dataType !== $expectedType)
				{
					throw new \RuntimeException(
						sprintf(
							"Failed to process Joomla API response (expected type “%s”, got “%s”): %s", $expectedType,
							$dataType,
							$body
						)
					);
				}
			}

			return $parsedBody->data;
		}

		throw new \RuntimeException('Failed to process Joomla API response (invalid data structure): ' . $body);
	}
}
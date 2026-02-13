<?php
/**
 * @package   joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license   AGPL-3.0-or-later
 */

declare(strict_types=1);

require_once dirname(__DIR__) . '/vendor/autoload.php';

// Define the version constant used by HttpProvider
if (!defined('MCP4JOOMLA_VERSION'))
{
	define('MCP4JOOMLA_VERSION', 'test');
}

/**
 * Creates a Joomla\Http\Response object from a status code and body string.
 *
 * @param   int          $statusCode  HTTP status code
 * @param   string|null  $body        Response body (typically JSON)
 * @param   array        $headers     Response headers
 *
 * @return  \Joomla\Http\Response
 */
function createJoomlaResponse(int $statusCode = 200, ?string $body = null, array $headers = []): \Joomla\Http\Response
{
	$stream = new \Laminas\Diactoros\Stream('php://temp', 'wb+');

	if ($body !== null)
	{
		$stream->write($body);
		$stream->rewind();
	}

	return new \Joomla\Http\Response($stream, $statusCode, $headers);
}

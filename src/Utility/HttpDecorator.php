<?php
/**
 * @package       joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license       AGPL-3.0-or-later
 */

namespace Dionysopoulos\Mcp4Joomla\Utility;

use Dionysopoulos\Mcp4Joomla\Container\Factory;
use Joomla\Http\Http;
use Joomla\Http\Response;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

/**
 * A decorator for the Joomla HTTP client to enable automatic logging of HTTP requests and responses.
 */
final class HttpDecorator implements ClientInterface
{
	use VarToLogTrait;

	private LoggerInterface $logger;

	public function __construct(
		private readonly Http $http,
		private readonly bool $logRequests = true,
		private readonly bool $logResponses = true
	)
	{
		$this->logger = Factory::getContainer()->get('log');
	}

	/**
	 * Retrieves the value of a specific option based on the provided key.
	 *
	 * @param   string      $key      The key identifying the option to retrieve.
	 * @param   mixed|null  $default  The default value to return if the option does not exist. Defaults to null.
	 *
	 * @return  mixed The value of the specified option, or the default value if the option does not exist.
	 */
	public function getOption(string $key, mixed $default = null): mixed
	{
		return $this->http->getOption($key, $default);
	}

	/**
	 * Sets the value of a specific option identified by the provided key.
	 *
	 * @param   string  $key    The key identifying the option to set.
	 * @param   mixed   $value  The value to assign to the specified option.
	 *
	 * @return  void
	 */
	public function setOption(string $key, mixed $value): void
	{
		$this->http->setOption($key, $value);
	}

	/**
	 * Sends an HTTP OPTIONS request to the specified URL with optional headers and timeout.
	 *
	 * @param   string    $url      The URL to send the OPTIONS request to.
	 * @param   array     $headers  An array of headers to include in the request. Defaults to an empty array.
	 * @param   int|null  $timeout  The timeout duration for the request in seconds. Defaults to null.
	 *
	 * @return  Response The server's response to the OPTIONS request.
	 */
	public function options(string $url, array $headers = [], ?int $timeout = null): Response
	{
		$this->logRequestData('OPTIONS', $url, $headers);

		$response = $this->http->options($url, $headers, $timeout);

		$this->logResponse($response);

		return $response;
	}

	/**
	 * Sends a HEAD request to the specified URL with the given headers and timeout.
	 *
	 * @param   string    $url      The URL to send the HEAD request to.
	 * @param   array     $headers  An array of headers to include in the request. Defaults to an empty array.
	 * @param   int|null  $timeout  The timeout for the request in seconds. If null, no timeout is applied.
	 *
	 * @return  Response The response object containing the result of the HEAD request.
	 */
	public function head(string $url, array $headers = [], ?int $timeout = null): Response
	{
		$this->logRequestData('HEAD', $url, $headers);

		$response = $this->http->head($url, $headers, $timeout);

		$this->logResponse($response);

		return $response;
	}

	/**
	 * Sends a GET request to the specified URL with optional headers and timeout.
	 *
	 * @param   string    $url        The URL to which the GET request is sent.
	 * @param   array     $headers    An associative array of headers to include in the request. Defaults to an empty
	 *                                array.
	 * @param   int|null  $timeout    The timeout duration for the request in seconds. Defaults to null, indicating no
	 *                                timeout.
	 *
	 * @return  Response  The response object received from the GET request.
	 */
	public function get(string $url, array $headers = [], ?int $timeout = null): Response
	{
		$this->logRequestData('GET', $url, $headers);

		$response = $this->http->get($url, $headers, $timeout);

		$this->logResponse($response);

		return $response;
	}

	/**
	 * Sends a POST request to the specified URL with the provided data and headers.
	 *
	 * @param   string    $url      The URL to send the POST request to.
	 * @param   mixed     $data     The data to include in the body of the POST request.
	 * @param   array     $headers  An array of headers to include in the request. Defaults to an empty array.
	 * @param   int|null  $timeout  The timeout duration for the request in seconds. Defaults to null.
	 *
	 * @return  Response  The response object resulting from the POST request.
	 */
	public function post(string $url, mixed $data, array $headers = [], ?int $timeout = null): Response
	{
		$this->logRequestData('POST', $url, $headers, $data);

		$response = $this->http->post($url, $data, $headers, $timeout);

		$this->logResponse($response);

		return $response;
	}

	/**
	 * Sends a PUT request to the specified URL with the provided data and headers.
	 *
	 * @param   string    $url      The URL to which the PUT request is sent.
	 * @param   mixed     $data     The data to be sent in the body of the request.
	 * @param   array     $headers  An array of headers to include with the request. Defaults to an empty array.
	 * @param   int|null  $timeout  The timeout duration for the request in seconds. Defaults to null for no timeout.
	 *
	 * @return  Response The response object received from the server.
	 */
	public function put(string $url, mixed $data, array $headers = [], ?int $timeout = null): Response
	{
		$this->logRequestData('PUT', $url, $headers, $data);

		$response = $this->http->put($url, $data, $headers, $timeout);

		$this->logResponse($response);

		return $response;
	}

	/**
	 * Sends a DELETE HTTP request to the specified URL with optional headers, timeout, and request data.
	 *
	 * @param   string      $url      The URL to which the DELETE request should be sent.
	 * @param   array       $headers  An optional array of headers to include in the request. Defaults to an empty
	 *                                array.
	 * @param   int|null    $timeout  An optional timeout value in seconds for the request. Defaults to null.
	 * @param   mixed|null  $data     Optional data to include with the DELETE request. Defaults to null.
	 *
	 * @return  Response The response returned from the DELETE HTTP request.
	 */
	public function delete(string $url, array $headers = [], ?int $timeout = null, mixed $data = null): Response
	{
		$this->logRequestData('DELETE', $url, $headers, $data);

		$response = $this->http->delete($url, $headers, $timeout, $data);

		$this->logResponse($response);

		return $response;
	}

	/**
	 * Sends a TRACE request to the specified URL with optional headers and timeout.
	 *
	 * @param   string    $url      The URL to send the TRACE request to.
	 * @param   array     $headers  An associative array of headers to include in the request. Defaults to an empty
	 *                              array.
	 * @param   int|null  $timeout  The timeout duration for the request. Defaults to null.
	 *
	 * @return  Response  The response object returned from the TRACE request.
	 */
	public function trace(string $url, array $headers = [], ?int $timeout = null): Response
	{
		$this->logRequestData('TRACE', $url, $headers);

		$response = $this->http->trace($url, $headers, $timeout);

		$this->logResponse($response);

		return $response;
	}

	/**
	 * Sends a PATCH request to the specified URL with the provided data and headers.
	 *
	 * @param   string    $url      The URL to send the PATCH request to.
	 * @param   mixed     $data     The data to include in the PATCH request body.
	 * @param   array     $headers  An array of headers to include in the request. Defaults to an empty array.
	 * @param   int|null  $timeout  The timeout duration for the request, if applicable. Defaults to null.
	 *
	 * @return  Response The response received from the PATCH request.
	 */
	public function patch(string $url, mixed $data, array $headers = [], ?int $timeout = null): Response
	{
		$this->logRequestData('PATCH', $url, $headers, $data);

		$response = $this->http->patch($url, $data, $headers, $timeout);

		$this->logResponse($response);

		return $response;
	}

	/** @inheritDoc */
	public function sendRequest(RequestInterface $request): ResponseInterface
	{
		$this->logRequest($request);

		$response = $this->http->sendRequest($request);

		$this->logResponse($response);

		return $response;
	}

	/**
	 * Logs details of an HTTP request including the method, URL, headers, and body data.
	 *
	 * @param   string  $method   The HTTP method used in the request (e.g., GET, POST).
	 * @param   string  $url      The URL of the request.
	 * @param   array   $headers  Optional. An associative array of headers sent with the request.
	 * @param   mixed   $data     Optional. The body data of the request, which can be a string, array, or an object.
	 *
	 * @return  void
	 * @throws  \JsonException
	 */
	private function logRequestData(string $method, string $url, array $headers = [], $data = null): void
	{
		if (!$this->logRequests)
		{
			return;
		}

		$this->logger->debug(sprintf('==> %s %s', $method, $url));

		if (!empty($headers))
		{
			$this->logger->debug('  HEADERS');

			foreach ($headers as $k => $v)
			{
				$this->logger->debug(sprintf('    %s: %s', $k, $this->varToLog($v)));
			}
		}

		if (empty($data))
		{
			return;
		}

		$this->logger->debug('  REQUEST BODY DATA');

		if ($data instanceof \Stringable)
		{
			$data = (string) $data;
		}
		elseif (is_object($data) && method_exists($data, '__toString'))
		{
			$data = (string) $data;
		}
		elseif (is_object($data) && $data instanceof \JsonSerializable)
		{
			$data = json_encode($data, JSON_THROW_ON_ERROR);
		}
		elseif (!is_array($data))
		{
			$data = $this->varToLog($data);
		}

		if (!is_array($data))
		{
			$this->logger->debug('    ' . $data);

			return;
		}

		foreach ($data as $k => $v)
		{
			$this->logger->debug(sprintf('    %s: %s', $k, $this->varToLog($v)));
		}
	}

	/**
	 * Logs the details of an HTTP request, including the method, URL, headers, and body content.
	 *
	 * @param   RequestInterface  $request  The HTTP request to be logged.
	 *
	 * @return void This method does not return any value.
	 */
	private function logRequest(RequestInterface $request): void
	{
		$request = clone $request;

		$method  = $request->getMethod();
		$url     = $request->getUri();
		$headers = $request->getHeaders();
		$data    = $request->getBody()?->getContents() ?: null;

		$this->logRequestData($method, $url, $headers, $data);
	}

	/**
	 * Logs the details of a given response, including the status code, reason phrase, headers, and body,
	 * if response logging is enabled.
	 *
	 * @param   Response  $response  The response object to log.
	 *
	 * @return  void  This method does not return a value.
	 */
	private function logResponse(Response $response): void
	{
		if (!$this->logResponses)
		{
			return;
		}

		$response = clone $response;

		$this->logger->debug(sprintf('<== %s %s', $response->getStatusCode(), $response->getReasonPhrase()));

		$headers = $response->headers;

		$this->logger->debug('  HEADERS');

		foreach ($headers as $k => $v)
		{
			$this->logger->debug(sprintf('    %s: %s', $k, $this->varToLog($v)));
		}

		$body = $response->getBody()?->getContents() ?: null;

		if (empty($body))
		{
			return;
		}

		$this->logger->debug('  RESPONSE BODY');

		$this->logger->debug($this->varToLog($body));
	}
}
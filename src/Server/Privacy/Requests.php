<?php
/**
 * @package       joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license       AGPL-3.0-or-later
 */

namespace Dionysopoulos\Mcp4Joomla\Server\Privacy;

use Dionysopoulos\Mcp4Joomla\Container\Factory;
use Dionysopoulos\Mcp4Joomla\Utility\AutoLoggingTrait;
use Dionysopoulos\Mcp4Joomla\Utility\GetDataFromResponseTrait;
use Dionysopoulos\Mcp4Joomla\Utility\HandleJoomlaAPIErrorTrait;
use Dionysopoulos\Mcp4Joomla\Utility\HttpDecorator;
use Dionysopoulos\Mcp4Joomla\Utility\VarToLogTrait;
use PhpMcp\Schema\ToolAnnotations;
use PhpMcp\Server\Attributes\McpTool;
use PhpMcp\Server\Attributes\Schema;

/**
 * MCP elements for Joomla! privacy requests management
 */
class Requests
{
	use HandleJoomlaAPIErrorTrait;
	use GetDataFromResponseTrait;
	use VarToLogTrait;
	use AutoLoggingTrait;

	#[McpTool(
		name: 'privacy_requests_list',
		description: 'List privacy requests',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function listRequests(
		#[Schema(description: 'Filter by request status')]
		?int $filterState = null
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/privacy/requests');

		if ($filterState !== null)
		{
			$uri->setVar('filter[status]', $filterState);
		}

		$response = $http->get($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'requests');
	}

	#[McpTool(
		name: 'privacy_requests_read',
		description: 'Retrieve a specific privacy request',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function readRequest(
		#[Schema(description: 'The ID of the privacy request to retrieve')]
		int $id
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/privacy/requests/' . $id);
		$response = $http->get($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'requests');
	}

	#[McpTool(
		name: 'privacy_requests_export',
		description: 'Export privacy request data',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function exportRequest(
		#[Schema(description: 'The ID of the privacy request to export')]
		int $id
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/privacy/requests/export/' . $id);
		$response = $http->get($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'requests');
	}

	#[McpTool(
		name: 'privacy_requests_create',
		description: 'Create a new privacy request'
	)]
	public function createRequest(
		#[Schema(description: 'Email address for the privacy request')]
		string $email,
		#[Schema(description: 'Request type: 1=export, 2=removal')]
		int $requestType
	)
	{
		$this->autologMCPTool();

		$postData = [
			'email'        => $email,
			'request_type' => $requestType,
		];

		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/privacy/requests');

		$response = $http->post($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'requests');
	}
}

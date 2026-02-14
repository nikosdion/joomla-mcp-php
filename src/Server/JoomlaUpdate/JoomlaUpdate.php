<?php
/**
 * @package       joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license       AGPL-3.0-or-later
 */

namespace Dionysopoulos\Mcp4Joomla\Server\JoomlaUpdate;

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
 * MCP elements for Joomla! update management
 */
class JoomlaUpdate
{
	use HandleJoomlaAPIErrorTrait;
	use GetDataFromResponseTrait;
	use VarToLogTrait;
	use AutoLoggingTrait;

	#[McpTool(
		name: 'joomlaupdate_healthcheck',
		description: 'Check if the Joomla update system is healthy',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function healthcheck()
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/joomlaupdate/healthcheck');
		$response = $http->get($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, null);
	}

	#[McpTool(
		name: 'joomlaupdate_getupdate',
		description: 'Get available Joomla update information',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function getUpdate()
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/joomlaupdate/update');
		$response = $http->get($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, null);
	}

	#[McpTool(
		name: 'joomlaupdate_prepare',
		description: 'Prepare the Joomla update for installation'
	)]
	public function prepare()
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/joomlaupdate/update/prepare');
		$response = $http->post($uri, '', ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, null);
	}

	#[McpTool(
		name: 'joomlaupdate_finalize',
		description: 'Finalize the Joomla update installation'
	)]
	public function finalize()
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/joomlaupdate/update/finalize');
		$response = $http->post($uri, '', ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, null);
	}

	#[McpTool(
		name: 'joomlaupdate_notify_success',
		description: 'Send notification that the Joomla update was successful'
	)]
	public function notifySuccess()
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/joomlaupdate/update/notify/success');
		$response = $http->post($uri, '', ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, null);
	}

	#[McpTool(
		name: 'joomlaupdate_notify_failed',
		description: 'Send notification that the Joomla update failed'
	)]
	public function notifyFailed()
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/joomlaupdate/update/notify/failed');
		$response = $http->post($uri, '', ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, null);
	}
}

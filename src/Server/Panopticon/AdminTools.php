<?php
/**
 * @package       joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license       AGPL-3.0-or-later
 */

namespace Dionysopoulos\Mcp4Joomla\Server\Panopticon;

use Dionysopoulos\Mcp4Joomla\Container\Factory;
use Dionysopoulos\Mcp4Joomla\Utility\AutoLoggingTrait;
use Dionysopoulos\Mcp4Joomla\Utility\GetDataFromResponseTrait;
use Dionysopoulos\Mcp4Joomla\Utility\HandleJoomlaAPIErrorTrait;
use Dionysopoulos\Mcp4Joomla\Utility\HttpDecorator;
use Dionysopoulos\Mcp4Joomla\Utility\VarToLogTrait;
use PhpMcp\Schema\ToolAnnotations;
use PhpMcp\Server\Attributes\McpTool;
use PhpMcp\Server\Attributes\Schema;

class AdminTools
{
	use HandleJoomlaAPIErrorTrait;
	use GetDataFromResponseTrait;
	use VarToLogTrait;
	use AutoLoggingTrait;

	#[McpTool(
		name: 'panopticon_admintools_unblock',
		description: 'Unblock an IP address from Admin Tools'
	)]
	public function unblock(
		#[Schema(description: 'The IP address to unblock')]
		?string $ip = null
	)
	{
		$this->autologMCPTool();

		$postData = [
			'ip' => $ip,
		];

		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/panopticon/admintools/unblock');

		$response = $http->post($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, null);
	}

	#[McpTool(
		name: 'panopticon_admintools_plugin_disable',
		description: 'Disable the Admin Tools system plugin'
	)]
	public function pluginDisable()
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/panopticon/admintools/plugin/disable');

		$response = $http->post($uri, '', ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, null);
	}

	#[McpTool(
		name: 'panopticon_admintools_plugin_enable',
		description: 'Enable the Admin Tools system plugin'
	)]
	public function pluginEnable()
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/panopticon/admintools/plugin/enable');

		$response = $http->post($uri, '', ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, null);
	}

	#[McpTool(
		name: 'panopticon_admintools_htaccess_disable',
		description: 'Disable the Admin Tools .htaccess protection'
	)]
	public function htaccessDisable()
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/panopticon/admintools/htaccess/disable');

		$response = $http->post($uri, '', ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, null);
	}

	#[McpTool(
		name: 'panopticon_admintools_htaccess_enable',
		description: 'Enable the Admin Tools .htaccess protection'
	)]
	public function htaccessEnable()
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/panopticon/admintools/htaccess/enable');

		$response = $http->post($uri, '', ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, null);
	}

	#[McpTool(
		name: 'panopticon_admintools_tempsuperuser',
		description: 'Create a temporary super user'
	)]
	public function tempSuperUser(
		#[Schema(description: 'Expiration time in seconds')]
		?int $expiration = null
	)
	{
		$this->autologMCPTool();

		$postData = [
			'expiration' => $expiration,
		];

		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/panopticon/admintools/tempsuperuser');

		$response = $http->post($uri, json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, null);
	}

	#[McpTool(
		name: 'panopticon_admintools_scanner_start',
		description: 'Start an Admin Tools security scan'
	)]
	public function scannerStart()
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/panopticon/admintools/scanner/start');

		$response = $http->post($uri, '', ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, null);
	}

	#[McpTool(
		name: 'panopticon_admintools_scanner_step',
		description: 'Execute a step of the Admin Tools security scan'
	)]
	public function scannerStep()
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/panopticon/admintools/scanner/step');

		$response = $http->post($uri, '', ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, null);
	}

	#[McpTool(
		name: 'panopticon_admintools_scans_list',
		description: 'List Admin Tools security scan results',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function scansList()
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/panopticon/admintools/scans');
		$response = $http->get($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, null);
	}

	#[McpTool(
		name: 'panopticon_admintools_scan_read',
		description: 'Read details of a specific security scan',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function scanRead(
		#[Schema(description: 'The ID of the security scan to retrieve')]
		int $id
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/panopticon/admintools/scans/' . $id);
		$response = $http->get($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, null);
	}

	#[McpTool(
		name: 'panopticon_admintools_scanalert_read',
		description: 'Read a specific scan alert',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function scanAlertRead(
		#[Schema(description: 'The ID of the security scan')]
		int $scanId,
		#[Schema(description: 'The ID of the scan alert')]
		int $alertId
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/panopticon/admintools/scans/' . $scanId . '/alerts/' . $alertId);
		$response = $http->get($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, null);
	}
}

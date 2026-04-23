<?php
/**
 * @package       joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license       AGPL-3.0-or-later
 */

declare(strict_types=1);

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

class CoreUpdate
{
	use HandleJoomlaAPIErrorTrait;
	use GetDataFromResponseTrait;
	use VarToLogTrait;
	use AutoLoggingTrait;

	#[McpTool(
		name: 'panopticon_coreupdate_status',
		description: 'Get Joomla core update status',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function status()
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/panopticon/core/update');
		$response = $http->get($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, null);
	}

	#[McpTool(
		name: 'panopticon_coreupdate_changesource',
		description: 'Change the Joomla core update source'
	)]
	public function changeSource(
		#[Schema(description: 'The update source URL')]
		string $source
	)
	{
		$this->autologMCPTool();

		$postData = [
			'source' => $source,
		];

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/panopticon/core/update/source');

		$response = $http->post($uri->toString(), json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, null);
	}

	#[McpTool(
		name: 'panopticon_coreupdate_download',
		description: 'Download the Joomla core update package'
	)]
	public function download()
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/panopticon/core/update/download');

		$response = $http->post($uri->toString(), '', ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, null);
	}

	#[McpTool(
		name: 'panopticon_coreupdate_download_chunked',
		description: 'Download the Joomla core update package in chunks'
	)]
	public function downloadChunked(
		#[Schema(description: 'Byte offset to start downloading from')]
		?int $offset = null,
		#[Schema(description: 'Number of bytes to download')]
		?int $length = null
	)
	{
		$this->autologMCPTool();

		$postData = [
			'offset' => $offset,
			'length' => $length,
		];

		$postData = array_filter($postData, fn($v) => $v !== null);

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/panopticon/core/update/download/chunked');

		$response = $http->post($uri->toString(), json_encode($postData), ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, null);
	}

	#[McpTool(
		name: 'panopticon_coreupdate_activate',
		description: 'Activate the downloaded Joomla core update'
	)]
	public function activate()
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/panopticon/core/update/activate');

		$response = $http->post($uri->toString(), '', ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, null);
	}

	#[McpTool(
		name: 'panopticon_coreupdate_disable',
		description: 'Disable the Joomla core update'
	)]
	public function disable()
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/panopticon/core/update/disable');

		$response = $http->post($uri->toString(), '', ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, null);
	}

	#[McpTool(
		name: 'panopticon_coreupdate_postupdate',
		description: 'Run post-update tasks for Joomla core update'
	)]
	public function postUpdate()
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/panopticon/core/update/postupdate');

		$response = $http->post($uri->toString(), '', ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, null);
	}

	#[McpTool(
		name: 'panopticon_coreupdate_checksum_prepare',
		description: 'Prepare file checksum verification for core update'
	)]
	public function checksumPrepare()
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/panopticon/core/update/checksum/prepare');

		$response = $http->post($uri->toString(), '', ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, null);
	}

	#[McpTool(
		name: 'panopticon_coreupdate_checksum_step',
		description: 'Execute a step of file checksum verification'
	)]
	public function checksumStep()
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/panopticon/core/update/checksum/step');

		$response = $http->post($uri->toString(), '', ['Content-Type' => 'application/json']);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, null);
	}
}

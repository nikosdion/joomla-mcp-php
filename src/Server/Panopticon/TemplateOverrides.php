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

class TemplateOverrides
{
	use HandleJoomlaAPIErrorTrait;
	use GetDataFromResponseTrait;
	use VarToLogTrait;
	use AutoLoggingTrait;

	#[McpTool(
		name: 'panopticon_templateoverrides_list',
		description: 'List changed template overrides',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function listOverrides()
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/panopticon/template/overrides/changed');
		$response = $http->get($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, null);
	}

	#[McpTool(
		name: 'panopticon_templateoverrides_read',
		description: 'Read details of a changed template override',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function readOverride(
		#[Schema(description: 'The ID of the changed template override to retrieve')]
		int $id
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/panopticon/template/overrides/changed/' . $id);
		$response = $http->get($uri);

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, null);
	}
}

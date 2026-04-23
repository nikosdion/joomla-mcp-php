<?php
/**
 * @package       joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license       AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Dionysopoulos\Mcp4Joomla\Server\Tickets;

use Dionysopoulos\Mcp4Joomla\Container\Factory;
use Dionysopoulos\Mcp4Joomla\Utility\AutoLoggingTrait;
use Dionysopoulos\Mcp4Joomla\Utility\GetDataFromResponseTrait;
use Dionysopoulos\Mcp4Joomla\Utility\HandleJoomlaAPIErrorTrait;
use Dionysopoulos\Mcp4Joomla\Utility\HttpDecorator;
use Dionysopoulos\Mcp4Joomla\Utility\VarToLogTrait;
use PhpMcp\Schema\ToolAnnotations;
use PhpMcp\Server\Attributes\McpTool;
use PhpMcp\Server\Attributes\Schema;

class Categories
{
	use HandleJoomlaAPIErrorTrait;
	use GetDataFromResponseTrait;
	use VarToLogTrait;
	use AutoLoggingTrait;

	#[McpTool(
		name: 'tickets_categories_list',
		description: 'List ATS (Akeeba Ticket System) ticket categories',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function listCategories(
		#[Schema(description: 'Search filter for categories')]
		?string $filterSearch = null,
		#[Schema(description: 'Maximum number of items to return per page', minimum: 1)]
		?int $pageLimit = null,
		#[Schema(description: 'Starting record offset for pagination (0-based)', minimum: 0)]
		?int $pageOffset = null
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http = Factory::getContainer()->get('http');
		$uri  = $http->getUri('v1/ats/categories');

		if ($filterSearch !== null)
		{
			$uri->setVar('filter[search]', $filterSearch);
		}

		if ($pageLimit !== null)
		{
			$uri->setVar('page[limit]', $pageLimit);
		}

		if ($pageOffset !== null)
		{
			$uri->setVar('page[offset]', $pageOffset);
		}

		$response = $http->get($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'categories');
	}

	#[McpTool(
		name: 'tickets_categories_read',
		description: 'Read details of a specific ATS (Akeeba Ticket System) ticket category',
		annotations: new ToolAnnotations(readOnlyHint: true)
	)]
	public function readCategory(
		#[Schema(description: 'The ID of the category to retrieve')]
		int $id
	)
	{
		$this->autologMCPTool();

		/** @var HttpDecorator $http */
		$http     = Factory::getContainer()->get('http');
		$uri      = $http->getUri('v1/ats/categories/' . $id);
		$response = $http->get($uri->toString());

		$this->handlePossibleJoomlaAPIError($response);

		return $this->getDataFromResponse($response, 'categories');
	}
}

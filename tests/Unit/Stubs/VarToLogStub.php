<?php
/**
 * @package   joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license   AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Dionysopoulos\Mcp4Joomla\Tests\Unit\Stubs;

use Dionysopoulos\Mcp4Joomla\Utility\VarToLogTrait;

class VarToLogStub
{
	use VarToLogTrait {
		varToLog as public;
	}
}

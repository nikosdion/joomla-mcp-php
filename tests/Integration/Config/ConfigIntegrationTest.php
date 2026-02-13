<?php
/**
 * @package   joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license   AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Dionysopoulos\Mcp4Joomla\Tests\Integration\Config;

use Dionysopoulos\Mcp4Joomla\Server\Config\Config;
use Dionysopoulos\Mcp4Joomla\Tests\Integration\IntegrationTestCase;

class ConfigIntegrationTest extends IntegrationTestCase
{
	private Config $configTool;

	protected function setUp(): void
	{
		parent::setUp();
		$this->configTool = new Config();
	}

	public function testReadApplicationConfig(): void
	{
		$result = $this->configTool->readApplicationConfig();

		$this->assertIsObject($result);
		$this->assertObjectHasProperty('data', $result);
	}

	public function testReadComponentConfig(): void
	{
		$result = $this->configTool->readComponentConfig('com_content');

		$this->assertIsObject($result);
		$this->assertObjectHasProperty('data', $result);
	}
}

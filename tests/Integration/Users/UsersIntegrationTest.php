<?php
/**
 * @package   joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license   AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Dionysopoulos\Mcp4Joomla\Tests\Integration\Users;

use Dionysopoulos\Mcp4Joomla\Server\Users\Users;
use Dionysopoulos\Mcp4Joomla\Tests\Integration\IntegrationTestCase;

class UsersIntegrationTest extends IntegrationTestCase
{
	private Users $users;

	protected function setUp(): void
	{
		parent::setUp();
		$this->users = new Users();
	}

	public function testListUsers(): void
	{
		$result = $this->users->listUsers();

		$this->assertIsObject($result);
		$this->assertObjectHasProperty('data', $result);
		$this->assertIsArray($result->data);
		// There should always be at least one user (the Super User)
		$this->assertNotEmpty($result->data);
	}
}

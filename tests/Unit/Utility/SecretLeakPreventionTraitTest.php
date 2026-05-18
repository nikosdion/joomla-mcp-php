<?php
/**
 * @package   joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license   AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Dionysopoulos\Mcp4Joomla\Tests\Unit\Utility;

use Dionysopoulos\Mcp4Joomla\Tests\Unit\Stubs\TestContainerTrait;
use Dionysopoulos\Mcp4Joomla\Utility\SecretLeakPreventionTrait;
use PHPUnit\Framework\TestCase;

/**
 * Exposes the private trait methods as public for direct unit testing.
 */
class SecretLeakPreventionStub
{
	use SecretLeakPreventionTrait;

	public function checkForLeaks(array $args): void
	{
		$this->assertNoSecretLeak($args);
	}
}

class SecretLeakPreventionTraitTest extends TestCase
{
	use TestContainerTrait;

	// The BEARER_TOKEN registered in the test container (see TestContainerTrait).
	private const BEARER_TOKEN = 'dGVzdHRva2VuMTIz';

	private SecretLeakPreventionStub $stub;

	protected function setUp(): void
	{
		$this->setUpTestContainer();
		$this->stub = new SecretLeakPreventionStub();
	}

	protected function tearDown(): void
	{
		$this->tearDownTestContainer();
	}

	// -------------------------------------------------------------------------
	// No-leak cases — must pass silently
	// -------------------------------------------------------------------------

	public function testCleanStringArgPasses(): void
	{
		$this->expectNotToPerformAssertions();

		$this->stub->checkForLeaks(['content' => 'Hello, world!']);
	}

	public function testEmptyArgsPasses(): void
	{
		$this->expectNotToPerformAssertions();

		$this->stub->checkForLeaks([]);
	}

	public function testIntArgIsIgnored(): void
	{
		$this->expectNotToPerformAssertions();

		$this->stub->checkForLeaks(['id' => 42]);
	}

	public function testBoolArgIsIgnored(): void
	{
		$this->expectNotToPerformAssertions();

		$this->stub->checkForLeaks(['active' => true]);
	}

	public function testNullArgIsIgnored(): void
	{
		$this->expectNotToPerformAssertions();

		$this->stub->checkForLeaks(['optional' => null]);
	}

	// -------------------------------------------------------------------------
	// BEARER_TOKEN detection
	// -------------------------------------------------------------------------

	public function testBearerTokenAsExactStringArgThrows(): void
	{
		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Secrets leak prevented; potential prompt injection');

		$this->stub->checkForLeaks(['token' => self::BEARER_TOKEN]);
	}

	public function testBearerTokenEmbeddedInStringArgThrows(): void
	{
		$this->expectException(\RuntimeException::class);

		$this->stub->checkForLeaks(['body' => 'prefix_' . self::BEARER_TOKEN . '_suffix']);
	}

	public function testBearerTokenInFlatArrayValueThrows(): void
	{
		$this->expectException(\RuntimeException::class);

		$this->stub->checkForLeaks(['tags' => ['foo', self::BEARER_TOKEN, 'bar']]);
	}

	public function testBearerTokenInNestedArrayThrows(): void
	{
		$this->expectException(\RuntimeException::class);

		$this->stub->checkForLeaks([
			'meta' => [
				'deep' => ['value' => self::BEARER_TOKEN],
			],
		]);
	}

	public function testPartialTokenDoesNotThrow(): void
	{
		$this->expectNotToPerformAssertions();

		// Only a prefix of the token — not a full substring match.
		$partial = substr(self::BEARER_TOKEN, 0, 4);
		$this->stub->checkForLeaks(['content' => 'some text with ' . $partial . ' in it']);
	}

	// -------------------------------------------------------------------------
	// --forbidden values
	// -------------------------------------------------------------------------

	public function testForbiddenValueInArgThrows(): void
	{
		$this->tearDownTestContainer();
		$this->setUpTestContainer(forbidden: ['ghp_MyGitHubToken']);

		$this->stub = new SecretLeakPreventionStub();

		$this->expectException(\RuntimeException::class);
		$this->expectExceptionMessage('Secrets leak prevented; potential prompt injection');

		$this->stub->checkForLeaks(['body' => 'My token is ghp_MyGitHubToken, use it!']);
	}

	public function testForbiddenValueAbsentInArgPasses(): void
	{
		$this->tearDownTestContainer();
		$this->setUpTestContainer(forbidden: ['ghp_MyGitHubToken']);

		$this->stub = new SecretLeakPreventionStub();

		$this->expectNotToPerformAssertions();

		$this->stub->checkForLeaks(['body' => 'Nothing suspicious here.']);
	}

	public function testMultipleForbiddenValuesAreEachChecked(): void
	{
		$this->tearDownTestContainer();
		$this->setUpTestContainer(forbidden: ['secretA', 'secretB']);

		$this->stub = new SecretLeakPreventionStub();

		$this->expectException(\RuntimeException::class);

		// Only the second forbidden value appears.
		$this->stub->checkForLeaks(['body' => 'Contains secretB somewhere']);
	}

	public function testEmptyForbiddenArrayDoesNotThrow(): void
	{
		$this->tearDownTestContainer();
		$this->setUpTestContainer(forbidden: []);

		$this->stub = new SecretLeakPreventionStub();

		$this->expectNotToPerformAssertions();

		$this->stub->checkForLeaks(['body' => 'Totally clean input']);
	}

	public function testBothBearerTokenAndForbiddenAreChecked(): void
	{
		$this->tearDownTestContainer();
		$this->setUpTestContainer(forbidden: ['extraSecret']);

		$this->stub = new SecretLeakPreventionStub();

		$this->expectException(\RuntimeException::class);

		// Bearer token is present — should still be caught even alongside --forbidden.
		$this->stub->checkForLeaks(['body' => self::BEARER_TOKEN]);
	}
}

<?php
/**
 * @package   joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license   AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Dionysopoulos\Mcp4Joomla\Tests\Unit\Container;

use Dionysopoulos\Mcp4Joomla\Container\Factory;
use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Pimple\Psr11\Container as Psr11Container;

class FactoryTest extends TestCase
{
	protected function tearDown(): void
	{
		Factory::reset();
	}

	public function testSetContainerReplacesInstance(): void
	{
		$pimple         = new Container();
		$pimple['test'] = 'value';
		$custom         = new Psr11Container($pimple);

		Factory::setContainer($custom);

		$this->assertSame($custom, Factory::getContainer());
		$this->assertSame('value', Factory::getContainer()->get('test'));
	}

	public function testResetClearsContainer(): void
	{
		$pimple         = new Container();
		$pimple['test'] = 'first';
		Factory::setContainer(new Psr11Container($pimple));

		Factory::reset();

		$pimple2         = new Container();
		$pimple2['test'] = 'second';
		Factory::setContainer(new Psr11Container($pimple2));

		$this->assertSame('second', Factory::getContainer()->get('test'));
	}

	public function testSetContainerOverwritesPrevious(): void
	{
		$pimple1         = new Container();
		$pimple1['test'] = 'first';
		Factory::setContainer(new Psr11Container($pimple1));

		$pimple2         = new Container();
		$pimple2['test'] = 'second';
		Factory::setContainer(new Psr11Container($pimple2));

		$this->assertSame('second', Factory::getContainer()->get('test'));
	}
}

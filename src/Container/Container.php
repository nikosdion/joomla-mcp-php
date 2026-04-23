<?php
/**
 * @package   joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license   AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Dionysopoulos\Mcp4Joomla\Container;

use Pimple\Container as PimpleContainer;

class Container extends PimpleContainer
{
	public function __construct(array $values = [])
	{
		parent::__construct($values);

		$this->register(new EnvironmentProvider());
		$this->register(new OptionsParserProvider());
		$this->register(new InputProvider());
		$this->register(new LogProvider());
		$this->register(new HttpProvider());
	}
}
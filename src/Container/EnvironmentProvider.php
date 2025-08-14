<?php
/**
 * @package       joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license       AGPL-3.0-or-later
 */

namespace Dionysopoulos\Mcp4Joomla\Container;

use Dotenv\Dotenv;
use Dotenv\Repository\Adapter as DotEnvAdapter;
use Dotenv\Repository\RepositoryBuilder as DotEnvRepoBuilder;
use Pimple\Container;
use Pimple\ServiceProviderInterface;

class EnvironmentProvider implements ServiceProviderInterface
{
	/**
	 * @inheritDoc
	 */
	public function register(Container $pimple)
	{
		$pimple['env'] = fn(Container $c) => Dotenv::create(
			repository: DotEnvRepoBuilder::createWithNoAdapters()
				->addAdapter(DotEnvAdapter\ServerConstAdapter::class)
				->addAdapter(DotEnvAdapter\PutenvAdapter::class)
				->immutable()
				->make(),
			paths: [__DIR__ . '/.env'],
			shortCircuit: false
		);
	}
}
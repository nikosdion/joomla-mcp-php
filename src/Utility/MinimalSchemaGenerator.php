<?php
/**
 * @package       joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license       AGPL-3.0-or-later
 */

declare(strict_types=1);

namespace Dionysopoulos\Mcp4Joomla\Utility;

use PhpMcp\Server\Utils\SchemaGenerator;

/**
 * A schema generator that strips Schema-attribute-derived fields from tool input schemas.
 *
 * Only preserves `type` and `default` for each property, and the top-level `required` array.
 * This reduces context size for LLMs with limited context windows.
 */
class MinimalSchemaGenerator extends SchemaGenerator
{
	public function generate(\ReflectionMethod|\ReflectionFunction $reflection): array
	{
		$schema = parent::generate($reflection);

		if (isset($schema['properties']) && is_array($schema['properties']))
		{
			foreach ($schema['properties'] as &$prop)
			{
				if (is_array($prop))
				{
					$minimal = [];

					if (isset($prop['type']))
					{
						$minimal['type'] = $prop['type'];
					}

					if (array_key_exists('default', $prop))
					{
						$minimal['default'] = $prop['default'];
					}

					$prop = $minimal;
				}
			}

			unset($prop);
		}

		return $schema;
	}
}

<?php
/**
 * @package       joomla-mcp-php
 * @copyright (c) 2025 Nicholas K. Dionysopoulos
 * @license       AGPL-3.0-or-later
 */

namespace Dionysopoulos\Mcp4Joomla\Container;

use DouglasGreen\OptParser\OptResult;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\RotatingFileHandler;
use Monolog\Level;
use Monolog\Logger;
use Monolog\LogRecord;
use Pimple\Container;
use Pimple\ServiceProviderInterface;
use Psr\Log\NullLogger;

class LogProvider implements ServiceProviderInterface
{
	public function register(Container $pimple)
	{
		$pimple['log'] = function (Container $c) {
			/** @var OptResult $input */
			$input = $c['input'];

			$defaultLogDirectory = __DIR__ . '/../../log';

			if ($input->log === null && !is_dir($defaultLogDirectory))
			{
				mkdir($defaultLogDirectory, 0755, true);
				$defaultLogDirectory = realpath($defaultLogDirectory);
			}

			$defaultLogFile = $input->log === null && !is_dir($defaultLogDirectory)
				? '/dev/null'
				: realpath($defaultLogDirectory) . '/debug.log';

			$rotatingFileHandler = new RotatingFileHandler(
				filename: $input->log ?: $defaultLogFile,
				maxFiles: 2,
				level: $input->debug ? Level::Debug : Level::Info
			);
			$rotatingFileHandler->pushProcessor(static function (LogRecord $record) {
				$record->extra['level_pad'] = str_pad($record->level->name, 7);
				return $record;
			});
			$rotatingFileHandler->setFormatter(new LineFormatter(
				format: "%datetime% %extra.level_pad% %message%\n",
				dateFormat: 'Y-m-d H:i:s',
				allowInlineLineBreaks: true,
				ignoreEmptyContextAndExtra: true
			));

			$handlers            = [$rotatingFileHandler];

			// If you ask to log into /dev/null, we disable logging by putting a blackhole logger in front of everything.
			if ($input->log === '/dev/null' || (empty($input->log) && $defaultLogFile === '/dev/null'))
			{
				array_unshift($handlers, new NullLogger());
			}

			return new Logger('joomla-mcp-php-debug', $handlers);
		};
	}
}
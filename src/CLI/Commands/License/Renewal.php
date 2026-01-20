<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\License;

use Kirby\CLI\CLI;
use Kirby\CLI\Command;

class Renewal extends Command
{
	public static function args(): array
	{
		return [
			'format' => [
				'prefix'       => 'f',
				'longPrefix'   => 'format',
				'description'  => 'The format for the renewal date (any format supported by PHP\'s `date()` function) or "timestamp"',
				'defaultValue' => 'Y-m-d'
			]
		];
	}

	public static function command(CLI $cli): void
	{
		$kirby = $cli->kirby();

		$license = $kirby->system()->license();
		$format  = $cli->arg('format');

		if (strtolower($format) === 'timestamp') {
			$format = null;
		}

		$renewal = $license->renewal(
			format: $format,
			handler: 'date'
		);

		if ($renewal === null) {
			$cli->error('No Kirby License is activated.');
			return;
		}

		$cli->success($renewal);
	}

	public static function description(): string|null
	{
		return 'Show the renewal date of the activated Kirby license.';
	}
}

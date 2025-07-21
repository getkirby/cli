<?php

declare(strict_types = 1);

use Kirby\CLI\CLI;

return [
	'description' => 'Show the renewal date of the activated Kirby license.',
	'args' => [
		'format' => [
			'prefix'       => 'f',
			'longPrefix'   => 'format',
			'description'  => 'The format for the renewal date (any format supported by PHP\'s `date()` function) or "timestamp"',
			'defaultValue' => 'Y-m-d'
		]
	],
	'command' => static function (CLI $cli): void {
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
];

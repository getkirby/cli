<?php

declare(strict_types = 1);

use Kirby\CLI\CLI;

return [
	'description' => 'Show the renewal date of the activated Kirby license.',
	'command' => static function (CLI $cli): void {
		$kirby = $cli->kirby();

		$license = $kirby->system()->license();

		$renewal = $license->renewal();

		if ($renewal === null) {
			$cli->error('No Kirby License is activated.');
			return;
		}

		$cli->success($renewal);
	}
];

<?php

declare(strict_types = 1);

use Kirby\CLI\CLI;

return [
	'description' => 'Prints the Kirby version',
	'command' => static function (CLI $cli): void {
		$kirby = $cli->kirby();
		$cli->success($kirby->version());
	}
];

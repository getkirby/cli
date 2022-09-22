<?php

declare(strict_types = 1);

use Kirby\CLI\CLI;
use Kirby\Filesystem\Dir;

return [
	'description' => 'Destroys all sessions',
	'command' => static function (CLI $cli): void {
		Dir::remove($cli->kirby()->root('sessions'));

		$cli->success('The sessions have been destroyed');
	}
];

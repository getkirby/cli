<?php

declare(strict_types = 1);

use Kirby\CLI\CLI;

return [
	'description' => 'Destroys all sessions',
	'command' => static function (CLI $cli): void {
		$cli->rmdir($cli->kirby()->root('sessions'));

		$cli->success('The sessions have been destroyed');
	}
];

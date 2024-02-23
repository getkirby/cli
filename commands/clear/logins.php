<?php

declare(strict_types = 1);

use Kirby\CLI\CLI;

return [
	'description' => 'Deletes the users `.logins` file',
	'command' => static function (CLI $cli): void {
		unlink($cli->kirby()->root('accounts') . '/.logins');

		$cli->success('The .logins file has been deleted');
	}
];

<?php

declare(strict_types = 1);

use Kirby\CLI\CLI;
use Kirby\Filesystem\F;

return [
	'description' => 'Deletes the users `.logins` file',
	'command' => static function (CLI $cli): void {
		F::remove($cli->kirby()->root('accounts') . '/.logins');

		$cli->success('The .logins file has been deleted');
	}
];

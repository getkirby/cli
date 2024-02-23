<?php

declare(strict_types = 1);

use Kirby\CLI\CLI;

return [
	'description' => 'Deletes the media folder',
	'command' => static function (CLI $cli): void {
		$cli->rmdir($cli->kirby()->root('media'));

		$cli->success('The media folder has been deleted');
	}
];

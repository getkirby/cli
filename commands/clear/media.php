<?php

declare(strict_types = 1);

use Kirby\CLI\CLI;
use Kirby\Filesystem\Dir;

return [
	'description' => 'Deletes the media folder',
	'command' => static function (CLI $cli): void {
		Dir::remove($cli->kirby()->root('media'));

		$cli->success('The media folder has been deleted');
	}
];

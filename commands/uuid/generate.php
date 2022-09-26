<?php

declare(strict_types = 1);

use Kirby\CLI\CLI;
use Kirby\Uuid\Uuids;

return [
	'description' => 'Creates all missing UUIDs',
	'command' => static function (CLI $cli): void {
		$kirby = $cli->kirby();

		if (version_compare($kirby->version(), '3.7.9', '<=') === true) {
			$cli->error('UUIDs are not available in your Kirby version. Please upgrade to 3.8.0');
			return;
		}

		Uuids::generate();

		$cli->success('UUIDs have been created');
	}
];

<?php

declare(strict_types = 1);

use Kirby\CLI\CLI;
use Kirby\Uuid\Uuids;

return [
	'description' => 'Removes all UUIDs',
	'command' => static function (CLI $cli): void {
		$cli->kirby();

		Uuids::each(
			function ($model) {
				$model->save(['uuid' => null]);
			}
		);

		Uuids::cache()->flush();

		$cli->success('All UUIDs have been removed');
	}
];

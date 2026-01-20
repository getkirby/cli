<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\UUID;

use Kirby\CLI\CLI;
use Kirby\CLI\Command;
use Kirby\Uuid\Uuids;

class Remove extends Command
{
	public static function command(CLI $cli): void
	{
		$cli->kirby();

		Uuids::each(
			function ($model) {
				$model->save(['uuid' => null]);
			}
		);

		Uuids::cache()->flush();

		$cli->success('All UUIDs have been removed');
	}

	public static function description(): string|null
	{
		return 'Removes all UUIDs';
	}
}

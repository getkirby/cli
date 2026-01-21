<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\UUID;

use Kirby\CLI\CLI;
use Kirby\CLI\Command;
use Kirby\Uuid\Uuids;

class Populate extends Command
{
	public static function command(CLI $cli): void
	{
		$kirby = $cli->kirby();

		if (version_compare($kirby->version(), '3.7.9', '<=') === true) {
			$cli->error('UUIDs are not available in your Kirby version. Please upgrade to 3.8.0');
			return;
		}

		Uuids::populate();

		$cli->success('UUID cache has been populated');
	}

	public static function description(): string|null
	{
		return 'Populates cache for all UUIDs';
	}
}

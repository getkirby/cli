<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Clear;

use Kirby\CLI\CLI;
use Kirby\CLI\Command;
use Kirby\Filesystem\Dir;

class Media extends Command
{
	public static function command(CLI $cli): void
	{
		Dir::remove($cli->kirby()->root('media'));

		$cli->success('The media folder has been deleted');
	}

	public static function description(): string|null
	{
		return 'Deletes the media folder';
	}
}

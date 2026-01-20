<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Clear;

use Kirby\CLI\CLI;
use Kirby\CLI\Command;
use Kirby\Filesystem\Dir;

class Sessions extends Command
{
	public static function command(CLI $cli): void
	{
		Dir::remove($cli->kirby()->root('sessions'));

		$cli->success('The sessions have been destroyed');
	}

	public static function description(): string|null
	{
		return 'Destroys all sessions';
	}
}

<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Clear;

use Kirby\CLI\CLI;
use Kirby\CLI\Command;
use Kirby\Filesystem\F;

class Logins extends Command
{
	public static function command(CLI $cli): void
	{
		F::remove($cli->kirby()->root('accounts') . '/.logins');

		$cli->success('The .logins file has been deleted');
	}

	public static function description(): string|null
	{
		return 'Deletes the users `.logins` file';
	}
}

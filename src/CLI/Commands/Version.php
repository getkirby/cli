<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands;

use Kirby\CLI\CLI;
use Kirby\CLI\Command;

class Version extends Command
{
	public static function command(CLI $cli): void
	{
		$kirby = $cli->kirby();
		$cli->success($kirby->version());
	}

	public static function description(): string|null
	{
		return 'Prints the Kirby version';
	}
}

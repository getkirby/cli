<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands;

use Kirby\CLI\CLI;
use Kirby\CLI\Command;

class Roots extends Command
{
	public static function command(CLI $cli): void
	{
		$cli->dump($cli->roots());
	}

	public static function description(): string|null
	{
		return 'Shows a list with all configured roots';
	}
}

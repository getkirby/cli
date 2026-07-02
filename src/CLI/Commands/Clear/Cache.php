<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Clear;

use Kirby\CLI\CLI;
use Kirby\CLI\Command;

class Cache extends Command
{
	public static function args(): array
	{
		return [
			'name' => [
				'description' => 'The name of the cache',
			]
		];
	}

	public static function command(CLI $cli): void
	{
		$kirby = $cli->kirby();
		$name  = $cli->argOrPrompt('name', 'Which cache should be emptied? (press <Enter> to clear the pages cache)', false);
		$name  = empty($name) === true ? 'pages' : $name;

		$kirby->cache($name)->flush();

		$cli->success('The cache has been cleared');
	}

	public static function description(): string|null
	{
		return 'Clears the cache';
	}
}

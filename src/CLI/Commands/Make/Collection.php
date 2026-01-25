<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Make;

use Kirby\CLI\CLI;
use Kirby\CLI\Command;

class Collection extends Command
{
	public static function args(): array
	{
		return [
			'name' => [
				'description' => 'The name of the collection',
			]
		];
	}

	public static function command(CLI $cli): void
	{
		$kirby = $cli->kirby();
		$name  = $cli->argOrPrompt('name', 'Enter a name for the collection:');
		$file  = $kirby->root('collections') . '/' . $name . '.php';

		$cli->make($file, $cli->root('commands.core') . '/make/_templates/collection.php');

		$cli->success('The collection has been created');
	}

	public static function description(): string|null
	{
		return 'Creates a new collection in site/collections';
	}
}

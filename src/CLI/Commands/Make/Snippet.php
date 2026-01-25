<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Make;

use Kirby\CLI\CLI;
use Kirby\CLI\Command;

class Snippet extends Command
{
	public static function args(): array
	{
		return [
			'name' => [
				'description' => 'The name of the snippet',
			]
		];
	}

	public static function command(CLI $cli): void
	{
		$kirby = $cli->kirby();
		$name  = $cli->argOrPrompt('name', 'Enter a name for the snippet:');
		$name  = lcfirst($name);
		$file  = $kirby->root('snippets') . '/' . $name . '.php';

		$cli->make($file, $cli->root('commands.core') . '/make/_templates/snippet.php', [
			'name' => $name
		]);

		$cli->success('The snippet has been created');
	}

	public static function description(): string|null
	{
		return 'Creates a new snippet in site/snippets';
	}
}

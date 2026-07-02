<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Make;

use Kirby\CLI\CLI;
use Kirby\CLI\Command;

class Plugin extends Command
{
	public static function args(): array
	{
		return [
			'name' => [
				'description' => 'The name of the plugin (`vendor/plugin`)',
			]
		];
	}

	public static function command(CLI $cli): void
	{
		$kirby = $cli->kirby();
		$name  = $cli->argOrPrompt('name', 'Enter a name (`vendor/plugin`) for the snippet:');
		$name  = lcfirst($name);
		$file  = $kirby->root('plugins') . '/' . basename($name) . '/index.php';

		$cli->make($file, $cli->root('commands.core') . '/make/_templates/plugin.php', [
			'name' => $name
		]);

		$cli->success('The plugin has been created');
	}

	public static function description(): string|null
	{
		return 'Creates a new plugin in site/plugins';
	}
}

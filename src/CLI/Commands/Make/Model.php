<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Make;

use Kirby\CLI\CLI;
use Kirby\CLI\Command;

class Model extends Command
{
	public static function args(): array
	{
		return [
			'name' => [
				'description' => 'The name of the model',
			]
		];
	}

	public static function command(CLI $cli): void
	{
		$kirby = $cli->kirby();
		$name  = $cli->argOrPrompt('name', 'Enter a name for the model:');
		$name  = lcfirst($name);
		$file  = $kirby->root('models') . '/' . $name . '.php';

		$cli->make($file, $cli->root('commands.core') . '/make/_templates/model.php', [
			'className' => ucfirst($name)
		]);

		$cli->success('The model has been created');
	}

	public static function description(): string|null
	{
		return 'Creates a new page model in site/models';
	}
}

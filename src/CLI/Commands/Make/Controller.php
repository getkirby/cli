<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Make;

use Kirby\CLI\CLI;
use Kirby\CLI\Command;

class Controller extends Command
{
	public static function args(): array
	{
		return [
			'name' => [
				'description' => 'The name of the controller',
			]
		];
	}

	public static function command(CLI $cli): void
	{
		$kirby = $cli->kirby();
		$name  = $cli->argOrPrompt('name', 'Enter a name for the controller:');
		$file  = $kirby->root('controllers') . '/' . $name . '.php';

		$cli->make($file, $cli->root('commands.core') . '/make/_templates/controller.php');

		$cli->success('The controller has been created');
	}

	public static function description(): string|null
	{
		return 'Creates a new template controller in site/controllers';
	}
}

<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Make;

use Kirby\CLI\CLI;
use Kirby\CLI\Command;

class Template extends Command
{
	public static function args(): array
	{
		return [
			'name' => [
				'description' => 'The name of the template',
			]
		];
	}

	public static function command(CLI $cli): void
	{
		$kirby = $cli->kirby();
		$name  = $cli->argOrPrompt('name', 'Enter a name for the template:');
		$name  = lcfirst(basename($name));
		$file  = $kirby->root('templates') . '/' . $name . '.php';

		$cli->make($file, $cli->root('commands.core') . '/make/_templates/template.php');

		$cli->success('The template has been created');
	}

	public static function description(): string|null
	{
		return 'Creates a new template in site/templates';
	}
}

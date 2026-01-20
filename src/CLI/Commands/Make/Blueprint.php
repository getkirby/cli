<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Make;

use Kirby\CLI\CLI;
use Kirby\CLI\Command;

class Blueprint extends Command
{
	public static function args(): array
	{
		return [
			'name' => [
				'description' => 'The name of the blueprint',
			]
		];
	}

	public static function command(CLI $cli): void
	{
		$kirby = $cli->kirby();
		$name  = $cli->argOrPrompt('name', 'Enter a name for the blueprint:');
		$file  = $kirby->root('blueprints') . '/' . $name . '.yml';

		$cli->make($file, 'title: {{ title }}', [
			'title' => ucfirst(basename($name))
		]);

		$cli->success('The blueprint has been created');
	}

	public static function description(): string|null
	{
		return 'Creates a new blueprint file in site/blueprints';
	}
}

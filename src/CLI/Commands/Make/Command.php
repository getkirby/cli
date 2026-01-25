<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Make;

use Kirby\CLI\CLI;
use Kirby\CLI\Command as BaseCommand;

class Command extends BaseCommand
{
	public static function args(): array
	{
		return [
			'name' => [
				'description' => 'The name of the command',
			],
			'global' => [
				'prefix'      => 'g',
				'longPrefix'  => 'global',
				'description' => 'Install the command globally',
				'noValue'     => true
			]
		];
	}

	public static function command(CLI $cli): void
	{
		$name = $cli->argOrPrompt('name', 'Enter a name for the command:');
		$name = str_replace(':', '/', $name);

		$root = $cli->arg('global') === true ? 'commands.global' : 'commands.local';
		$file = $cli->root($root) . '/' . $name . '.php';

		$cli->make($file, $cli->root('commands.core') . '/make/_templates/command.php');
		$cli->success('The command has been created: ' . $file);
	}

	public static function description(): string|null
	{
		return 'Creates a new local command for the Kirby CLI';
	}
}

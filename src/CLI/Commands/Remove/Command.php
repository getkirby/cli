<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Remove;

use Kirby\CLI\CLI;
use Kirby\CLI\Command as BaseCommand;

class Command extends BaseCommand
{
	public static function args(): array
	{
		return [
			'name' => [
				'description' => 'The name of the command',
			]
		];
	}

	public static function command(CLI $cli): void
	{
		$name = $cli->argOrPrompt('name', 'Enter a name for the command:');
		$name = str_replace(':', '/', $name);

		$global = $cli->root('commands.global') . '/' . $name . '.php';
		$local  = $cli->root('commands.local') . '/' . $name . '.php';

		$files = [];

		if (is_file($global) === true) {
			$files[] = $global;
		}

		if (is_file($local) === true) {
			$files[] = $local;
		}

		if (count($files) > 1) {
			$input = $cli->checkboxes('Which commands do you want to delete?:', $files);
			$trash = $input->prompt();
		} else {
			$trash = $files;
		}

		foreach ($trash as $file) {
			unlink($file);
		}

		if (count($trash) === 1) {
			$cli->success('The command has been deleted');
		} else {
			$cli->success('The commands have been deleted');
		}
	}

	public static function description(): string|null
	{
		return 'Removes a custom command';
	}
}

<?php

declare(strict_types = 1);

use Kirby\CLI\CLI;

return [
	'description' => 'Creates a new local command for the Kirby CLI',
	'args' => [
		'name' => [
			'description' => 'The name of the command',
		],
		'global' => [
			'prefix'       => 'g',
			'longPrefix'   => 'global',
			'description'  => 'Install the command globally',
			'noValue'      => true
		]
	],
	'command' => static function (CLI $cli): void {
		$name = $cli->argOrPrompt('name', 'Enter a name for the command:');
		$name = str_replace(':', '/', $name);

		$root = $cli->arg('global') === true ? 'commands.global' : 'commands.local';
		$file = $cli->root($root) . '/' . $name . '.php';

		$cli->make($file, __DIR__ . '/_templates/command.php');
		$cli->success('The command has been created: ' . $file);
	}
];

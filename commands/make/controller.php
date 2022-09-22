<?php

declare(strict_types = 1);

use Kirby\CLI\CLI;

return [
	'description' => 'Creates a new template controller in site/controllers',
	'args' => [
		'name' => [
			'description' => 'The name of the controller',
		]
	],
	'command' => static function (CLI $cli): void {
		$kirby = $cli->kirby();
		$name  = $cli->argOrPrompt('name', 'Enter a name for the controller:');
		$file  = $kirby->root('controllers') . '/' . $name . '.php';

		$cli->make($file, __DIR__ . '/_templates/controller.php');

		$cli->success('The controller has been created');
	}
];

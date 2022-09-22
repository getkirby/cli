<?php

declare(strict_types = 1);

use Kirby\CLI\CLI;

return [
	'description' => 'Creates a new page model in site/models',
	'args' => [
		'name' => [
			'description' => 'The name of the model',
		]
	],
	'command' => static function (CLI $cli): void {
		$kirby = $cli->kirby();
		$name  = $cli->argOrPrompt('name', 'Enter a name for the model:');
		$name  = lcfirst($name);
		$file  = $kirby->root('models') . '/' . $name . '.php';

		$cli->make($file, __DIR__ . '/_templates/model.php', [
			'className' => ucfirst($name)
		]);

		$cli->success('The model has been created');
	}
];

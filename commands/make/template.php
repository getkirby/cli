<?php

declare(strict_types = 1);

use Kirby\CLI\CLI;

return [
	'description' => 'Creates a new template in site/templates',
	'args' => [
		'name' => [
			'description' => 'The name of the template',
		]
	],
	'command' => static function (CLI $cli): void {
		$kirby = $cli->kirby();
		$name  = $cli->argOrPrompt('name', 'Enter a name for the template:');
		$name  = lcfirst(basename($name));
		$file  = $kirby->root('templates') . '/' . $name . '.php';

		$cli->make($file, __DIR__ . '/_templates/template.php');

		$cli->success('The template has been created');
	}
];

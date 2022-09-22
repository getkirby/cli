<?php

declare(strict_types = 1);

use Kirby\CLI\CLI;

return [
	'description' => 'Creates a new blueprint file in site/blueprints',
	'args' => [
		'name' => [
			'description' => 'The name of the blueprint',
		]
	],
	'command' => static function (CLI $cli): void {
		$kirby = $cli->kirby();
		$name  = $cli->argOrPrompt('name', 'Enter a name for the blueprint:');
		$file  = $kirby->root('blueprints') . '/' . $name . '.yml';

		$cli->make($file, 'title: {{ title }}', [
			'title' => ucfirst(basename($name))
		]);

		$cli->success('The blueprint has been created');
	}
];

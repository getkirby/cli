<?php

declare(strict_types = 1);

use Kirby\CLI\CLI;

return [
	'description' => 'Creates a new collection in site/collections',
	'args' => [
		'name' => [
			'description' => 'The name of the collection',
		]
	],
	'command' => static function (CLI $cli): void {
		$kirby = $cli->kirby();
		$name  = $cli->argOrPrompt('name', 'Enter a name for the collection:');
		$file  = $kirby->root('collections') . '/' . $name . '.php';

		$cli->make($file, __DIR__ . '/_templates/collection.php');

		$cli->success('The collection has been created');
	}
];

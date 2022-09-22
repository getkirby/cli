<?php

declare(strict_types = 1);

use Kirby\CLI\CLI;

return [
	'description' => 'Creates a new snippet in site/snippets',
	'args' => [
		'name' => [
			'description' => 'The name of the snippet',
		]
	],
	'command' => static function (CLI $cli): void {
		$kirby = $cli->kirby();
		$name  = $cli->argOrPrompt('name', 'Enter a name for the snippet:');
		$name  = lcfirst($name);
		$file  = $kirby->root('snippets') . '/' . $name . '.php';

		$cli->make($file, __DIR__ . '/_templates/snippet.php', [
			'name' => $name
		]);

		$cli->success('The snippet has been created');
	}
];

<?php

declare(strict_types = 1);

use Kirby\CLI\CLI;

return [
	'description' => 'Creates a new plugin in site/plugins',
	'args' => [
		'name' => [
			'description' => 'The name of the plugin (`vendor/plugin`)',
		]
	],
	'command' => static function (CLI $cli): void {
		$kirby = $cli->kirby();
		$name  = $cli->argOrPrompt('name', 'Enter a name (`vendor/plugin`) for the snippet:');
		$name  = lcfirst($name);
		$file  = $kirby->root('plugins') . '/' . basename($name) . '/index.php';

		$cli->make($file, __DIR__ . '/_templates/plugin.php', [
			'name' => $name
		]);

		$cli->success('The plugin has been created');
	}
];

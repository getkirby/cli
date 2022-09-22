<?php

declare(strict_types = 1);

use Kirby\CLI\CLI;

return [
	'description' => 'Creates a new config file in site/config',
	'args' => [
		'domain' => [
			'description' => 'An optional domain for a multi-environment config',
		]
	],
	'command' => static function (CLI $cli): void {
		$kirby  = $cli->kirby();
		$domain = $cli->arg('domain');
		$name   = empty($domain) === false ? 'config.' . $domain : 'config';
		$file   = $kirby->root('config') . '/' . $name . '.php';

		$cli->make($file, __DIR__ . '/_templates/config.php');

		$cli->success('The config "' . basename($file) . '" has been created');
	}
];

<?php

declare(strict_types = 1);

use Kirby\CLI\CLI;

return [
	'description' => 'Installs the kirby folder',
	'args' => [
		'version' => [
			'description'  => 'The version corresponding with the tag name in the repo',
			'defaultValue' => 'latest'
		]
	],
	'command' => static function (CLI $cli): void {
		$cli->out('Installing Kirby (' . $cli->arg('version') . ') …');
		$cli->run('install:repo', 'getkirby/kirby', 'kirby', '--version=' . $cli->arg('version'));
		$cli->success('Kirby has been installed');
	}
];

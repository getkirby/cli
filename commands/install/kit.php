<?php

declare(strict_types = 1);

use Kirby\CLI\CLI;

return [
	'description' => 'Installs a Kirby Kit in a subfolder',
	'args' => [
		'kit' => [
			'description' => 'The name of the kit (starterkit, demokit, plainkit)',
		],
		'folder' => [
			'description' => 'The name of folder the kit should be installed into',
		]
	],
	'command' => static function (CLI $cli): void {
		$kit = $cli->arg('kit');

		if (empty($kit) === true) {
			$input = $cli->radio('Which kit do you want to install?', [
				'starterkit',
				'plainkit',
				'demokit',
			]);

			$kit = $input->prompt();
		}

		$kit  ??= 'starterkit';
		$folder = $cli->argOrPrompt('folder', 'Enter a folder name (press <Enter> to use "' . $kit . '")', false);
		$title  = ucfirst($kit);

		if (empty($folder) === true) {
			$folder = $kit;
		}

		$cli->out('Installing Kirby ' . $title . ' …');
		$cli->run('install:repo', 'getkirby/' . $kit, $folder);
		$cli->success('The Kirby ' . $title . ' has been installed');
	}
];

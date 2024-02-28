<?php

declare(strict_types = 1);

use Kirby\CLI\CLI;
use Kirby\Filesystem\Dir;

return [
	'description' => 'Upgrades a Kirby plugin',
	'args' => [
		'repo' => [
			'description' => 'The Kirby plugin registry name (i.e. getkirby/kql)',
			'required'    => true
		],
		'version' => [
			'description'  => 'The version corresponding with the tag name in the repo',
			'defaultValue' => 'latest'
		]
	],
	'command' => static function (CLI $cli): void {
		$repo = $cli->arg('repo');
		$version = $cli->arg('version');

		if ($plugin = $cli->kirby()->plugin($repo)) {
			try {
				// move plugin directory to prevent overwrite
				Dir::move($plugin->root(), $plugin->root() . '.bak');
				$cli->run('plugin:install', $repo, $version);
				Dir::remove($plugin->root() . '.bak');
				$cli->success('The ' . $repo . ' plugin has been updated to ' . $version . ' version');
			} catch (Throwable) {
				Dir::move($plugin->root() . '.bak', $plugin->root());
				$cli->error('The ' . $repo . ' plugin could not updated');
			}
		} else {
			$cli->error('The ' . $repo . ' plugin could not found');
		}
	}
];

<?php

declare(strict_types = 1);

use Kirby\CLI\CLI;
use Kirby\Filesystem\Dir;

return [
	'description' => 'Removes a Kirby plugin',
	'args' => [
		'repo' => [
			'description' => 'The Kirby plugin registry name (i.e. getkirby/kql)',
			'required'    => true
		]
	],
	'command' => static function (CLI $cli): void {
		$repo = $cli->arg('repo');

		if ($plugin = $cli->kirby()->plugin($repo)) {
			Dir::remove($plugin->root());
			$cli->success('The ' . $repo . ' plugin has been removed');
		} else {
			$cli->error('The ' . $repo . ' plugin could not be found');
		}
	}
];

<?php

declare(strict_types = 1);

use Kirby\CLI\CLI;

return [
	'description' => 'Clears the cache',
	'args' => [
		'name' => [
			'description' => 'The name of the cache',
		]
	],
	'command' => static function (CLI $cli): void {
		$kirby = $cli->kirby();
		$name  = $cli->argOrPrompt('name', 'Which cache should be emptied? (press <Enter> to clear the pages cache)', false);
		$name  = empty($name) === true ? 'pages' : $name;

		$kirby->cache($name)->flush();

		$cli->success('The cache has been cleared');
	}
];

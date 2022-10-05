<?php

declare(strict_types = 1);

use Kirby\CLI\CLI;

return [
	'description' => 'Shows a list with all configured roots',
	'command' => static function (CLI $cli): void {
		$cli->dump($cli->roots());
	}
];

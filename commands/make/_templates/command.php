<?php

return [
	'description' => 'Nice command',
	'args' => [],
	'command' => static function ($cli): void {
		$cli->success('Nice command!');
	}
];

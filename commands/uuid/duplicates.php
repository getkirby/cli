<?php

declare(strict_types = 1);

use Kirby\CLI\Commands\UUID\Duplicates;

return [
	'description' => 'Find and optionally fix duplicate UUIDs',
	'args' => [
		'fix' => [
			'description' => 'Fix duplicate UUIDs by generating new ones',
			'noValue'     => true,
		],
	],
	'command' => Duplicates::command(...)
];

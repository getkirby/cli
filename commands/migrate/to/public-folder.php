<?php

declare(strict_types = 1);

use Kirby\CLI\Commands\Migrate\To\PublicFolder;

return [
	'description' => 'Switch to a public folder setup',
	'command'     => PublicFolder::command(...)
];

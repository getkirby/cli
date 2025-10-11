<?php

declare(strict_types = 1);

use Kirby\CLI\Commands\Migrate\To\RootFolder;

return [
	'description' => 'Switch to a root folder setup',
	'command'     => RootFolder::command(...)
];

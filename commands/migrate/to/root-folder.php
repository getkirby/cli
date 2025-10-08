<?php

declare(strict_types = 1);

use Kirby\CLI\Commands\Migrate\To\RootFolder;

return [
	'description' => 'Switch to a rooter folder setup',
	'command'     => RootFolder::command(...)
];

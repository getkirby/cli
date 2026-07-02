<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands;

use Kirby\CLI\CLI;
use Kirby\CLI\Command;

class Install extends Command
{
	public static function args(): array
	{
		return [
			'version' => [
				'description'  => 'The version corresponding with the tag name in the repo',
				'defaultValue' => 'latest'
			]
		];
	}

	public static function command(CLI $cli): void
	{
		$cli->out('Installing Kirby (' . $cli->arg('version') . ') …');
		$cli->run('install:repo', 'getkirby/kirby', 'kirby', '--version=' . $cli->arg('version'));
		$cli->success('Kirby has been installed');
	}

	public static function description(): string|null
	{
		return 'Installs the kirby folder';
	}
}

<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Make;

use Kirby\CLI\CLI;
use Kirby\CLI\Command;

class Config extends Command
{
	public static function args(): array
	{
		return [
			'domain' => [
				'description' => 'An optional domain for a multi-environment config',
			]
		];
	}

	public static function command(CLI $cli): void
	{
		$kirby  = $cli->kirby();
		$domain = $cli->arg('domain');
		$name   = empty($domain) === false ? 'config.' . $domain : 'config';
		$file   = $kirby->root('config') . '/' . $name . '.php';

		$cli->make($file, $cli->root('commands.core') . '/make/_templates/config.php');

		$cli->success('The config "' . basename($file) . '" has been created');
	}

	public static function description(): string|null
	{
		return 'Creates a new config file in site/config';
	}
}

<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Plugin;

use Kirby\CLI\CLI;
use Kirby\CLI\Command;
use Kirby\Filesystem\Dir;

class Remove extends Command
{
	public static function args(): array
	{
		return [
			'repo' => [
				'description' => 'The Kirby plugin registry name (i.e. getkirby/kql)',
				'required'    => true
			]
		];
	}

	public static function command(CLI $cli): void
	{
		$repo = $cli->arg('repo');

		if ($plugin = $cli->kirby()->plugin($repo)) {
			Dir::remove($plugin->root());
			$cli->success('The ' . $repo . ' plugin has been removed');
		} else {
			$cli->error('The ' . $repo . ' plugin could not be found');
		}
	}

	public static function description(): string|null
	{
		return 'Removes a Kirby plugin';
	}
}

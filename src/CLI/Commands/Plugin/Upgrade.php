<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Plugin;

use Kirby\CLI\CLI;
use Kirby\CLI\Command;
use Kirby\Filesystem\Dir;
use Throwable;

class Upgrade extends Command
{
	public static function args(): array
	{
		return [
			'repo' => [
				'description' => 'The Kirby plugin registry name (i.e. getkirby/kql)',
				'required'    => true
			],
			'version' => [
				'description'  => 'The version corresponding with the tag name in the repo',
				'defaultValue' => 'latest'
			]
		];
	}

	public static function command(CLI $cli): void
	{
		$repo    = $cli->arg('repo');
		$version = $cli->arg('version');

		if ($plugin = $cli->kirby()->plugin($repo)) {
			try {
				// move plugin directory to prevent overwrite
				Dir::move($plugin->root(), $plugin->root() . '.bak');
				$cli->run('plugin:install', $repo, $version);
				Dir::remove($plugin->root() . '.bak');
				$cli->success('The ' . $repo . ' plugin has been updated to ' . $version . ' version');
			} catch (Throwable) {
				Dir::move($plugin->root() . '.bak', $plugin->root());
				$cli->error('The ' . $repo . ' plugin could not updated');
			}
		} else {
			$cli->error('The ' . $repo . ' plugin could not found');
		}
	}

	public static function description(): string|null
	{
		return 'Upgrades a Kirby plugin';
	}
}

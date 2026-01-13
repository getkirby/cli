<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands;

use Kirby\CLI\CLI;
use Kirby\CLI\Command;

class Help extends Command
{
	public static function command(CLI $cli): void
	{
		$commands = $cli->commands();

		$cli->bold('Kirby CLI ' . $cli->version());
		$cli->br();
		$cli->out('Core commands:');

		foreach ($commands['core'] as $command) {
			$cli->out('- kirby ' . $command);
		}

		if (count($commands['global']) > 0) {
			$cli->br();
			$cli->out('Global commands:');

			foreach ($commands['global'] as $command) {
				$cli->out('- kirby ' . $command);
			}
		}

		if (count($commands['custom']) > 0) {
			$cli->br();
			$cli->out('Custom commands:');

			foreach ($commands['custom'] as $command) {
				$cli->out('- kirby ' . $command);
			}
		}

		if (count($commands['plugins']) > 0) {
			$cli->br();
			$cli->out('Plugin commands:');

			foreach ($commands['plugins'] as $command) {
				$cli->out('- kirby ' . $command);
			}
		}

		$cli->br();

		$cli->success('Have fun with the Kirby CLI!');
	}

	public static function description(): string|null
	{
		return 'Prints help for the Kirby CLI';
	}
}

<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Clear;

use Kirby\CLI\CLI;
use Kirby\CLI\Command;
use Kirby\Filesystem\F;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class Lock extends Command
{
	public static function command(CLI $cli): void
	{
		$path              = $cli->kirby()->root('content');
		$directoryIterator = new RecursiveDirectoryIterator($path);
		$iterator          = new RecursiveIteratorIterator($directoryIterator);
		$counter           = 0;

		foreach ($iterator as $file) {
			if ($file->getFilename() === '.lock') {
				F::remove($file->getPathName());
				$counter++;
			}
		}

		$cli->success($counter . ' lock file(s) have been deleted');
	}

	public static function description(): string|null
	{
		return 'Deletes the content `.lock` files';
	}
}

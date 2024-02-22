<?php

declare(strict_types = 1);

use Kirby\CLI\CLI;
use Kirby\Filesystem\F;

return [
	'description' => 'Deletes the content `.lock` files',
	'command' => static function (CLI $cli): void {
		$path 				= $cli->kirby()->root('content');
		$directoryIterator 	= new RecursiveDirectoryIterator($path);
		$iterator 			= new RecursiveIteratorIterator($directoryIterator);
		$counter            = 0;

		foreach ($iterator as $file) {
			if ($file->getFilename() === '.lock') {
				F::remove($file->getPathName());
				$counter++;
			}
		}

		$cli->success($counter . ' lock file(s) have been deleted');
	}
];

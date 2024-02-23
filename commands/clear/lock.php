<?php

declare(strict_types = 1);

use Kirby\CLI\CLI;

return [
	'description' => 'Deletes the content `.lock` files',
	'command' => static function (CLI $cli): void {
		$path 				= $cli->kirby()->root('content');
		$directoryIterator 	= new RecursiveDirectoryIterator($path);
		$iterator 			= new RecursiveIteratorIterator($directoryIterator);
		$counter            = 0;

		foreach ($iterator as $file) {
			if ($file->getFilename() === '.lock') {
				unlink($file->getPathName());
				$counter++;
			}
		}

		$cli->success($counter . ' lock file(s) have been deleted');
	}
];

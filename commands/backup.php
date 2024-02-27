<?php

declare(strict_types = 1);

use Kirby\CLI\CLI;
use Kirby\Filesystem\Dir;

return [
	'description' => 'Creates backup of application files',
	'args' => [
		'root' => [
			'description' => 'Selects the kirby root, which should be backuped'
		]
	],
	'command' => static function (CLI $cli): void {
		if (class_exists('ZipArchive') === false) {
			throw new Exception('ZipArchive library could not be found');
		}

		$root = $cli->argOrPrompt(
			'root',
			'Which root should be backuped? (press <Enter> to backup your entire site)',
			false
		);

		$root     = empty($root) === true ? 'index' : $root;
		$rootPath = $cli->kirby()->root($root);

		if ($rootPath === null) {
			throw new Exception('Invalid root entered: ' . $root);
		}

		if (is_dir($rootPath) === false) {
			throw new Exception('The root does not exist: ' . $root);
		}

		$kirbyPath  = $cli->kirby()->root('index');
		$backupPath = $kirbyPath . '/backup';
		$backupFile = $backupPath . '/' . $root . '-' . date('Y-m-d-His') . '.zip';

		if (is_file($backupFile) === true) {
			throw new Exception('The backup file exists');
		}

		// create backup directory before the process
		Dir::make($backupPath);

		$zip = new ZipArchive();
		if ($zip->open($backupFile, ZipArchive::CREATE) !== true) {
			throw new Exception('Failed to create backup file');
		}

		$files = new RecursiveIteratorIterator(
			new RecursiveCallbackFilterIterator(
				new RecursiveDirectoryIterator(
					$rootPath,
					FilesystemIterator::SKIP_DOTS
				),
				fn ($file) => $file->isFile() || in_array($file->getBaseName(), ['.git', 'backup']) === false
			)
		);

		foreach ($files as $file) {
			// skip directories, will be added automatically
			if ($file->isDir() === false) {
				// get real and relative path for current file
				$filePath = $file->getRealPath();
				$relativePath = substr($filePath, strlen($rootPath) + 1);

				// add current file to archive
				$zip->addFile($filePath, $relativePath);
			}
		}

		if ($zip->close() === false) {
			throw new Exception('There was a problem writing the backup file');
		}

		$cli->success('The backup has been created: ' . substr($backupFile, strlen($kirbyPath)));
	}
];

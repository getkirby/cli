<?php

declare(strict_types = 1);

use Kirby\CLI\CLI;
use Kirby\Toolkit\Str;

return [
	'description' => 'Creates backup of application files',
	'args' => [
		'root' => [
			'description' => 'Selects the kirby root to be backup'
		]
	],
	'command' => static function (CLI $cli): void {
		$root       = $cli->argOrPrompt(
			'root',
			'Which root should be backup? (press <Enter> to backup the entire kirby application)',
			false
		);
		$root 	    = empty($root) === true ? 'index' : $root;
		$targetPath = $cli->kirby()->root($root);

		if ($targetPath === null) {
			throw new Exception('Invalid root entered: ' . $root);
		}

		$kirbyPath          = $cli->kirby()->root('index');
		$backupPath         = $kirbyPath . '/backup';
		$backupFile         = $backupPath . '/' . date('Y-m-d-His') . '.zip';
		$relativeBackupFile = Str::after($backupFile, $kirbyPath);
		$relativeTargetPath = trim(Str::after($targetPath, $kirbyPath), '/');

		// execution commands list
		$commands = [
			// navigates to the target directory to ignore parent folders in zip file
			'cd ' . escapeshellarg($targetPath) . ';',
			// set backup file path
			'zip -r ' . escapeshellarg($backupFile),
			// set target backup directory
			escapeshellarg(empty($relativeTargetPath) === true ? '*' : ('./' . $relativeTargetPath . '/*')),
			// exclude backup directory
			'-x ' . escapeshellarg('backup/*')
		];

		exec(implode(' ', $commands));

		$cli->success('The backup has been created: ' . $relativeBackupFile);
	}
];

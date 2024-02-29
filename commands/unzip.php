<?php

declare(strict_types = 1);

use Kirby\CLI\CLI;

return [
	'description' => 'Extracts a zip file',
	'args' => [
		'file' => [
			'description' => 'The file to unzip',
			'required' => true
		],
		'to' => [
			'description' => 'The place to extract the zip to',
			'required' => true
		]
	],
	'command' => static function (CLI $cli): void {
		if (class_exists('ZipArchive') === false) {
			throw new Exception('ZipArchive library could not be found');
		}

		$file = $cli->arg('file');
		$to   = $cli->arg('to');
		$temp = $to . '.temp';

		if (is_file($file) === false) {
			throw new Exception('The ZIP file does not exist');
		}

		if (is_dir($to) === true || is_dir($temp) === true) {
			throw new Exception('The target directory exists');
		}

		// extract the zip file to the temp directory
		// to move temp directory to target directory
		// since there is not a php native function to move entire directory into parent
		$zipArchive = new ZipArchive();
		if ($zipArchive->open($file) === true) {
			$zipArchive->extractTo($temp);
			$zipArchive->close();
		} else {
			throw new Exception('The zip file could not read');
		}

		$temp = realpath($temp);

		// target path doesn't exist yet and realpath won't work for it. So use temp path.
		$to   = substr($temp, 0, strlen($temp) - strlen('.temp'));

		// find the archive folder in that tmp dir
		$archive = glob($temp . '/*', GLOB_ONLYDIR)[0] ?? null;

		if (is_dir($archive) === false) {
			throw new Exception('The archive directory could not be found');
		}

		rename($archive, $to);
		$cli->rmdir($to . '.temp');
	}
];

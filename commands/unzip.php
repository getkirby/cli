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
		$file = $cli->arg('file');
		$to   = $cli->arg('to');

		if (is_file($file) === false) {
			throw new Exception('The ZIP file does not exist');
		}

		if (is_dir($to) === true) {
			throw new Exception('The target directory exists');
		}

		// extract the zip file
		exec('unzip ' . $file . ' -d ' . $to);

		$to = realpath($to);

		// find the archive folder in that tmp dir
		$archive = glob($to . '/*', GLOB_ONLYDIR)[0] ?? null;

		if (is_dir($archive) === false) {
			throw new Exception('The archive directory could not be found');
		}

		exec('mv ' . $to . '/*/{.[!.],}* ' . $to . '/');
		exec('rm -rf ' . $archive);
	}
];

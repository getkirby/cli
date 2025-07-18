<?php

declare(strict_types = 1);

use Kirby\CLI\CLI;

return [
	'description' => 'Displays Kirby license information in table or JSON format.',
	'args' => [
		'format' => [
			'description' => 'Output format: table or json.',
			'prefix' => 'f',
			'longPrefix' => 'format'
		]
	],
	'command' => static function (CLI $cli): void {
		$kirby = $cli->kirby();

		$supportedFormats = [
			'table',
			'json'
		];

		$format = $cli->arg('format');
		$format = $format ? $format : 'table';

		if (!in_array($format, $supportedFormats)) {
			$cli->error('Invalid format. Supported formats are: ' . implode(', ', $supportedFormats));
			return;
		}

		$license = $kirby->system()->license();

		$data = $license->content();
		$data['renewal'] = $license->renewal();

		// Print License info as table
		if ($format === 'table') {
			$cli->success('Kirby License Information:');
			$tableData = [];
			foreach ($data as $key => $value) {
				$tableData[] = ['Key' => $key, 'Value' => $value ?? '—'];
			}

			$cli->climate()->table($tableData);
		} else {
			// Print License info as JSON
			$cli->climate()->json($data);
		}
	}
];

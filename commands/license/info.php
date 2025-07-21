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

		$licenseFields = [
			'activation',
			'code',
			'date',
			'domain',
			'email'
		];

		$format = $cli->arg('format');
		$format = $format ? $format : 'table';

		if (in_array($format, $supportedFormats) === false) {
			$cli->error('Invalid format. Supported formats are: ' . implode(', ', $supportedFormats));
			return;
		}

		$license = $kirby->system()->license();
		$data    = $license->content();

		// Only include the fields we want to display
		$data = array_intersect_key($data, array_flip($licenseFields));
		$data['renewal'] = $license->renewal(format: 'Y-m-d H:i:s', handler: 'date');

		// Print License info as table
		if ($format === 'table') {
			$cli->success('Kirby License Information:');
			$tableData = [];
			foreach ($data as $key => $value) {
				$tableData[] = ['Key' => $key, 'Value' => $value ?? '—'];
			}

			// Use the Climate library to create a table for better readability
			$cli->climate()->table($tableData);
		} else {
			// Print License info as JSON
			$cli->climate()->json($data);
		}
	}
];

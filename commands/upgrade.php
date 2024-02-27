<?php

declare(strict_types = 1);

use Kirby\CLI\CLI;

return [
	'description' => 'Upgrades the Kirby core',
	'args' => [
		'version' => [
			'description'  => 'The version corresponding with the tag name in the kirby repo',
			'defaultValue' => 'latest'
		]
	],
	'command' => static function (CLI $cli): void {
		$version   = $cli->arg('version');
		$kirby     = $cli->kirby();
		$kirbyRoot = $kirby->root('kirby');
		$folder    = 'kirby.' . $version;

		// if entered version is `latest`
		// get exact version to compare current kirby version
		if ($version === 'latest') {
			$release = json_decode(file_get_contents('https://getkirby.com/security.json'));
			$version = $release->latest;
		}

		// checks current kirby version whether same or higher
		if (version_compare($version, $kirby->version(), '<=') === true) {
			throw new Exception('Current Kirby version is the same or higher than the version you are trying to upgrade to');
		}

		// confirms the process when major version upgrade available
		if ((int)$version > (int)$kirby->version()) {
			$majorConfirm = $cli->prompt('Major version upgrade detected. Are you sure you want to proceed? Please type <Yes> or <Y> and press <Enter> to proceed:');

			if (in_array(strtolower($majorConfirm), ['yes', 'y']) === false) {
				throw new Exception('Major version upgrade has been canceled');
			}
		}

		if (is_dir($cli->dir() . '/' . $folder) === true) {
			throw new Exception('The ' . $folder . ' directory exists');
		}

		$cli->out('Upgrading Kirby from ' . $kirby->version() . ' to ' . $version . ' …');
		$cli->run('install:repo', 'getkirby/kirby', $folder, $version);

		// move current kirby to temp directory as backup
		rename($kirbyRoot, $kirbyRoot . '.old');

		// move new kirby to current root
		rename($cli->dir() . '/' . $folder, $kirbyRoot);

		// delete old kirby
		$cli->rmdir($kirbyRoot . '.old');

		// delete temp panel directory
		$cli->rmdir($kirby->root('media') . '/panel');

		$cli->success('The Kirby has been upgraded to ' . $version);
	}
];

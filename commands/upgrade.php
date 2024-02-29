<?php

declare(strict_types = 1);

use Kirby\CLI\CLI;
use Kirby\Filesystem\Dir;

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

		// checks if the current kirby version is the same as the new one
		if (version_compare($version, $kirby->version(), '==') === true) {
			$cli->success('Your Kirby installation is already up to date');
			exit;
		}

		// checks current kirby version and prevents downgrade
		if (version_compare($kirby->version(), $version, '>') === true) {
			throw new Exception('Your current Kirby version is higher than the version you are trying to upgrade to');
		}

		// confirms the process when major version upgrade available
		if ((int)$version > (int)$kirby->version()) {
			$confirm = $cli->confirm('Major version upgrade detected. Are you sure you want to proceed?');

			if ($confirm->confirmed() === false) {
				throw new Exception('Major version upgrade has been canceled');
			}
		}

		$cli->out('Upgrading Kirby from ' . $kirby->version() . ' to ' . $version . ' …');
		$cli->run('install:repo', 'getkirby/kirby', $folder, '--version=' . $version);

		// move current kirby to temp directory as backup
		Dir::move($kirbyRoot, $kirbyRoot . '.old');

		// move new kirby to current root
		Dir::move($cli->dir() . '/' . $folder, $kirbyRoot);

		// delete old kirby
		Dir::remove($kirbyRoot . '.old');

		// delete temp panel directory
		Dir::remove($kirby->root('media') . '/panel');

		$cli->success('Kirby has been upgraded to ' . $version);
	}
];

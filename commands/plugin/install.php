<?php

declare(strict_types = 1);

use Kirby\CLI\CLI;
use Kirby\Filesystem\F;

return [
	'description' => 'Installs a kirby plugin repository from the Github',
	'args' => [
		'repo' => [
			'description' => 'The Github repo path (i.e. getkirby/kql)',
			'required'    => true
		],
		'version' => [
			'description'  => 'The version corresponding with the tag name in the repo',
			'defaultValue' => 'latest'
		]
	],
	'command' => static function (CLI $cli): void {
		$repo    = $cli->arg('repo');
		$version = $cli->arg('version');
		$archive = 'https://github.com/' . $repo . '/archive';

		if ($version === 'latest') {
			$url = $archive . '/main.zip';
		} else {
			$url = $archive . '/refs/tags/' . $cli->arg('version') . '.zip';
		}

		list($vendor, $plugin) = explode('/', $repo);

		$zip = $cli->dir() . '/' . $vendor . '-' . $plugin . '-' . time() . '.zip';
		$dir = $cli->kirby()->root('plugins') . '/' . $plugin;

		$cli->confirmToDelete($zip, 'The zip file exists. Do you want to delete it?');
		$cli->confirmToDelete($dir, 'The directory exists. Do you want to delete it?');

		$cli->out('Installing ' . $repo . ' plugin …');

		// download the zip file
		$cli->run('download', $url, $zip);

		// unzip the repo
		$cli->run('unzip', $zip, $dir);

		// remove the zip
		F::unlink($zip);

		$cli->success('The ' . $repo . ' plugin has been installed ' . $version . ' version');
	}
];

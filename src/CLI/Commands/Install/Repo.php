<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Install;

use Kirby\CLI\CLI;
use Kirby\CLI\Command;

class Repo extends Command
{
	public static function args(): array
	{
		return [
			'repo' => [
				'description' => 'The Github repo path (i.e. getkirby/kirby)',
				'required'    => true
			],
			'folder' => [
				'description' => 'The name of folder the repo should be installed into',
			],
			'version' => [
				'prefix'       => 'v',
				'longPrefix'   => 'version',
				'description'  => 'The version corresponding with the tag name in the repo',
				'defaultValue' => 'latest'
			]
		];
	}

	public static function command(CLI $cli): void
	{
		$repo   = $cli->arg('repo');
		$folder = $cli->arg('folder');

		if (empty($folder) === true) {
			$folder = basename($repo);
		}

		$archive = 'https://github.com/' . $repo . '/archive';

		if ($cli->arg('version') === 'latest') {
			$url = $archive . '/main.zip';
		} else {
			$url = $archive . '/refs/tags/' . $cli->arg('version') . '.zip';
		}

		$zip = $cli->dir() . '/' . $folder . '-' . time() . '.zip';
		$dir = $cli->dir() . '/' . $folder;

		$cli->confirmToDelete($zip, 'The zip file exists. Do you want to delete it?');
		$cli->confirmToDelete($dir, 'The directory exists. Do you want to delete it?');

		// download the zip file
		$cli->run('download', $url, $zip);

		// unzip the repo
		$cli->run('unzip', $zip, $dir);

		// remove the zip
		unlink($zip);
	}

	public static function description(): string|null
	{
		return 'Downloads a repository from the getkirby org on Github';
	}
}

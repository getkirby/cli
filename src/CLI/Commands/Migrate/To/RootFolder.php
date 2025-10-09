<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Migrate\To;

use Kirby\CLI\CLI;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;

class RootFolder extends PublicFolder
{
	public static function command(CLI $cli): void
	{
		$dir = $cli->dir();

		// A valid kirby installation is needed
		$cli->kirby();

		static::confirmMigration($cli);

		$cli->out('Migrating to a root folder setup …');
		$cli->br();

		$publicDir = static::publicDir($dir);

		if (is_dir($publicDir) === false) {
			$cli->error('The public folder directory does not exist');
			return;
		}

		$dirs  = Dir::dirs($publicDir, null, true);
		$files = Dir::files($publicDir, ['index.php'], true);

		static::moveDirs($cli, $dir, $dirs);
		static::moveFiles($cli, $dir, $files);

		static::makeIndexPHP($cli, $dir);
		static::removePublicDir($cli, $publicDir);

		$cli->br();
		$cli->success('Migrated to a root folder setup');
	}

	protected static function makeIndexPHP(CLI $cli, string $dir)
	{
		$template = $cli->root('commands.core') . '/migrate/to/_templates/index.root.simple.php';

		$cli->make($dir . '/index.php', $template);

		$cli->out('✅ The new index.php has been created');

		// remove the old index.php
		F::remove($dir . '/public/index.php');
	}

	protected static function removePublicDir(CLI $cli, string $publicDir): void
	{
		if (Dir::isEmpty($publicDir) === false) {
			$cli->out('🚨 The public directory is not empty yet. Please make sure to move remaining files and directories yourself.');
			return;
		}

		if (Dir::remove($publicDir) === true) {
			$cli->out('✅ The public directory has been removed');
		} else {
			$cli->out('🚨 The public directory could not been removed');
		}
	}

}

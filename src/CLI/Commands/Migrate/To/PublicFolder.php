<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Migrate\To;

use Kirby\CLI\CLI;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;

class PublicFolder
{
	public static function command(CLI $cli): void
	{
		$dir = $cli->dir();

		// A valid kirby installation is needed
		$cli->kirby();

		static::confirmMigration($cli);

		$cli->out('Migrating to a public folder setup …');
		$cli->br();

		$publicDir = static::publicDir($dir);

		static::makePublicDir($cli, $publicDir);

		static::moveDirs($cli, $publicDir, static::movableDirs($dir));
		static::moveFiles($cli, $publicDir, static::movableFiles($dir));

		static::makeIndexPHP($cli, $publicDir);
		static::removeOldIndexPHP($cli);

		$cli->br();
		$cli->success('Migrated to a public folder setup');
	}

	protected static function confirmMigration(CLI $cli): void
	{
		$cli->br();
		$cli->confirmToContinue("💡 Migrating your folder setup can lead to a broken site.\n\nMake sure to backup your current installation. If you have modified your index.php you might need to adjust the new index.php after the migration.\n\nDo you want to continue?");
		$cli->br();
	}

	protected static function makeIndexPHP(CLI $cli, string $publicDir)
	{
		$template = $cli->root('commands.core') . '/migrate/to/_templates/index.public.simple.php';

		$cli->make($publicDir . '/index.php', $template);

		$cli->out('✅ The index.php has been created');
	}

	protected static function makePublicDir(CLI $cli, string $publicDir): void
	{
		if (is_dir($publicDir) === true) {
			$cli->confirmToContinue('⚠️  The public folder exists. Do you still want to continue?');
			$cli->br();
		}

		Dir::make($publicDir);

		$cli->out('✅ The public folder has been created');
	}

	protected static function movableDirs(string $dir): array
	{
		return [
			$dir . '/.well-known',
			$dir . '/assets',
			$dir . '/media',
		];
	}

	protected static function movableFiles(string $dir): array
	{
		return [
			$dir . '/.htaccess',
			$dir . '/favicon.ico',
			$dir . '/favicon.png',
			$dir . '/favicon.svg',
			$dir . '/favicon.gif',
			$dir . '/robots.txt',
		];
	}

	protected static function moveDirs(CLI $cli, string $destination, array $dirs): void
	{
		foreach ($dirs as $dir) {
			if (is_dir($dir) === false) {
				continue;
			}

			$dirname = basename($dir);
			$target  = $destination . '/' . $dirname;

			// avoid overwriting directories that should not be overwritten
			if (is_dir($target) === true) {
				$input = $cli->confirm('⚠️  The directory ' . $dirname . ' exists. Do you want to overwrite it?');
				$cli->br();

				if ($input->confirmed() === false) {
					continue;
				}
			}

			Dir::remove($target);

			if (Dir::move($dir, $target) === true) {
				$cli->out('✅ The ' . $dirname . ' directory has been moved');
			} else {
				$cli->out('🚨 The ' . $dirname . ' directory could not be moved');
			}
		}
	}

	protected static function moveFiles(CLI $cli, string $destination, array $files): void
	{
		foreach ($files as $file) {
			if (is_file($file) === false) {
				continue;
			}

			$filename = basename($file);
			$target   = $destination . '/' . $filename;

			// avoid overwriting directories that should not be overwritten
			if (is_dir($target) === true) {
				$input = $cli->confirm('⚠️  The file ' . $filename . ' exists. Do you want to overwrite it?');
				$cli->br();

				if ($input->confirmed() === false) {
					continue;
				}
			}

			F::remove($target);

			if (F::move($file, $destination . '/' . $filename) === true) {
				$cli->out('✅ The ' . $filename . ' file has been moved');
			} else {
				$cli->out('🚨 The ' . $filename . ' file could not be moved');
			}
		}
	}

	protected static function publicDir(string $dir): string
	{
		return $dir . '/public';
	}

	protected static function removeOldIndexPHP(CLI $cli): void
	{
		$file = $cli->dir() . '/index.php';

		if (is_file($file) === false) {
			return;
		}

		if (F::remove($file)) {
			$cli->out('✅ The old index.php has been removed');
		} else {
			$cli->out('🚨 The old index.php could not been removed');
		}
	}

}

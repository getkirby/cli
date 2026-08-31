<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Migrate\To;

use Kirby\CLI\CLI;
use Kirby\CLI\Command;
use Kirby\Filesystem\Dir;
use Kirby\Filesystem\F;
use stdClass;

class PublicFolder extends Command
{
	public static function description(): string
	{
		return 'Switch to a public folder setup';
	}

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
		static::updateComposerConfig($cli);

		$cli->br();
		$cli->success('Migrated to a public folder setup');
	}

	protected static function confirmMigration(CLI $cli): void
	{
		$cli->br();
		$cli->confirmToContinue("💡 Migrating your folder setup can lead to a broken site.\n\nMake sure to backup your current installation. If you have modified your index.php you might need to adjust the new index.php after the migration.\n\nDo you want to continue?");
		$cli->br();
	}

	/**
	 * The document root that the built-in server has to
	 * serve after the migration. Null if there is none.
	 */
	protected static function documentRoot(CLI $cli): string|null
	{
		return basename(static::publicDir($cli->dir()));
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

	/**
	 * Points the start script in the composer.json
	 * at the document root of the new setup
	 */
	protected static function updateComposerConfig(CLI $cli): void
	{
		$file = $cli->dir() . '/composer.json';

		if (is_file($file) === false) {
			return;
		}

		$contents = F::read($file);

		if (is_string($contents) === false) {
			$cli->out('🚨 The composer.json could not be read');
			return;
		}

		// decoding into objects keeps empty objects, like an
		// `"extra": {}`, from turning into arrays when writing back
		$composer = json_decode($contents);

		if ($composer instanceof stdClass === false) {
			$cli->out('🚨 The composer.json could not be parsed');
			return;
		}

		$start = $composer->scripts->start ?? null;

		if ($start === null) {
			return;
		}

		// the start script can be a single command or a list of commands
		$commands = is_string($start) === true ? [$start] : $start;

		if (is_array($commands) === false) {
			return;
		}

		$root    = static::documentRoot($cli);
		$server  = false;
		$updated = false;

		foreach ($commands as $key => $command) {
			if (is_string($command) === false) {
				continue;
			}

			$new = static::updateStartCommand($command, $root);

			// the command does not start a server
			if ($new === null) {
				continue;
			}

			$server = true;

			if ($new !== $command) {
				$commands[$key] = $new;
				$updated        = true;
			}
		}

		if ($server === false) {
			$cli->out('🚨 The start script in the composer.json could not be updated. Please set the document root manually.');
			return;
		}

		// the start script already serves the right document root
		if ($updated === false) {
			return;
		}

		$composer->scripts->start = is_string($start) === true ? $commands[0] : $commands;

		$json = $cli->json($composer);

		// keep the trailing newline of the original file
		if (str_ends_with($contents, "\n") === true) {
			$json .= "\n";
		}

		if (F::write($file, $json) === true) {
			$cli->out('✅ The composer.json has been updated');
		} else {
			$cli->out('🚨 The composer.json could not be updated');
		}
	}

	/**
	 * Sets the document root of a command that starts the
	 * built-in server, or removes it if there is no document
	 * root. Returns null if the command starts no server.
	 */
	protected static function updateStartCommand(
		string $command,
		string|null $root
	): string|null {
		$parts = preg_split('!\s+!', trim($command)) ?: [];

		// only the built-in server takes a document root
		if (in_array('-S', $parts, true) === false) {
			return null;
		}

		// remove the current document root
		$target = array_search('-t', $parts, true);

		if ($target !== false) {
			array_splice($parts, $target, 2);
		}

		// add the new document root right after the host and port
		if ($root !== null) {
			$server = array_search('-S', $parts, true);

			if ($server !== false) {
				array_splice($parts, $server + 2, 0, ['-t', $root]);
			}
		}

		return implode(' ', $parts);
	}

}

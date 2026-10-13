<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Migrate\To;

use Kirby\CLI\CLI;

/**
 * Opens up the protected migration helpers for testing
 */
class RootFolderProxy extends RootFolder
{
	public static function documentRoot(CLI $cli): string|null
	{
		return parent::documentRoot($cli);
	}

	public static function updateComposerConfig(CLI $cli): void
	{
		parent::updateComposerConfig($cli);
	}

	public static function updateStartCommand(
		string $command,
		string|null $root
	): string|null {
		return parent::updateStartCommand($command, $root);
	}
}

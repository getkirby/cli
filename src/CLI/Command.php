<?php

declare(strict_types = 1);

namespace Kirby\CLI;

/**
 * Abstract base class for CLI commands
 *
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
abstract class Command
{
	/**
	 * Optional command arguments
	 * Override this method in your command class to define arguments
	 * @return array<string, mixed>
	 */
	public static function args(): array
	{
		return [];
	}

	/**
	 * The main command execution method
	 * Must be implemented by all command classes
	 */
	abstract public static function command(CLI $cli): void;

	/**
	 * Optional command description
	 * Override this method in your command class to provide a description
	 */
	public static function description(): string|null
	{
		return null;
	}
}

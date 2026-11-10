<?php

declare(strict_types = 1);

namespace Kirby\CLI;

use League\CLImate\CLImate;
use League\CLImate\TerminalObject\Dynamic\Confirm;
use League\CLImate\Util\Reader\Stream;

/**
 * Testable CLI subclass that allows Kirby injection for unit testing
 */
class TestableCLI extends CLI
{
	/**
	 * Answers to the next confirmation prompts (e.g. `y`),
	 * which are read from stdin otherwise
	 */
	public array $answers = [];

	public function __construct(
		object|null $kirby = null,
		array $roots = []
	) {
		$this->climate = new CLImate();
		$this->roots = $roots;

		if ($kirby !== null) {
			$this->kirby = $kirby;
			$this->roots = array_merge($kirby->roots()->toArray(), $roots);
		}

		$this->createCommandRoots();
	}

	public function confirm(string $message): Confirm
	{
		$reader = null;

		if ($this->answers !== []) {
			$reader = new Stream('data://text/plain,' . array_shift($this->answers));
		}

		return $this->climate->confirm($message, $reader);
	}
}

<?php

declare(strict_types = 1);

namespace Kirby\CLI;

use League\CLImate\CLImate;

/**
 * Testable CLI subclass that allows Kirby injection for unit testing
 */
class TestableCLI extends CLI
{
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
}

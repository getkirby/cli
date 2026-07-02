<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands;

use Kirby\CLI\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Roots::class)]
class RootsTest extends TestCase
{
	public function testArgs(): void
	{
		$this->assertSame([], Roots::args());
	}

	public function testCommand(): void
	{
		$cli = $this->createCLI();
		$this->setupOutputCapture();

		Roots::command($cli);

		// Should output the commands roots
		$this->assertOutputContains('commands.core');
		$this->assertOutputContains('commands.global');
		$this->assertOutputContains('commands.local');
	}

	public function testCommandWithCustomRoots(): void
	{
		$customRoots = [
			'custom' => '/path/to/custom'
		];

		$cli = $this->createCLI(roots: $customRoots);
		$this->setupOutputCapture();

		Roots::command($cli);

		$this->assertOutputContains('custom');
		$this->assertOutputContains('/path/to/custom');
	}

	public function testCommandWithKirbyRoots(): void
	{
		$cli = $this->createCLIWithKirby();
		$this->setupOutputCapture();

		Roots::command($cli);

		// Should include Kirby roots
		$this->assertOutputContains('index');
		$this->assertOutputContains('content');
		$this->assertOutputContains('site');
	}

	public function testDescription(): void
	{
		$this->assertSame('Shows a list with all configured roots', Roots::description());
	}
}

<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands;

use Kirby\CLI\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Help::class)]
class HelpTest extends TestCase
{
	public function setUp(): void
	{
		// Change to Fixtures directory for consistent command discovery
		chdir(__DIR__ . '/../fixtures');
	}

	public function testArgs(): void
	{
		$this->assertSame([], Help::args());
	}

	public function testCommand(): void
	{
		$cli = $this->createCLI();
		$this->setupOutputCapture();

		Help::command($cli);

		$this->assertOutputContains('Kirby CLI');
		$this->assertOutputContains('Core commands:');
		$this->assertOutputContains('Have fun with the Kirby CLI!');
	}

	public function testCommandShowsCoreCommands(): void
	{
		$cli = $this->createCLI();
		$this->setupOutputCapture();

		Help::command($cli);

		// Should list some core commands
		$this->assertOutputContains('kirby help');
		$this->assertOutputContains('kirby version');
		$this->assertOutputContains('kirby install');
	}

	public function testCommandShowsVersionNumber(): void
	{
		$cli = $this->createCLI();
		$this->setupOutputCapture();

		Help::command($cli);

		// Should show CLI version (matches semver pattern)
		$output = $this->getOutput();
		$this->assertMatchesRegularExpression('/Kirby CLI \d+\.\d+\.\d+/', $output);
	}

	public function testDescription(): void
	{
		$this->assertSame('Prints help for the Kirby CLI', Help::description());
	}
}

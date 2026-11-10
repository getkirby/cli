<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands;

use Kirby\CLI\TestableCLI;
use Kirby\CLI\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Help::class)]
class HelpTest extends TestCase
{
	/**
	 * Runs the command with the --raw option
	 * and returns the printed lines
	 */
	protected function rawOutput(TestableCLI $cli): array
	{
		$cli->climate()->arguments->add(Help::args());
		$cli->climate()->arguments->parse(['kirby', '--raw']);
		$this->setupOutputCapture($cli);

		Help::command($cli);

		return explode(PHP_EOL, trim($this->getOutput()));
	}

	protected function setUp(): void
	{
		// Change to Fixtures directory for consistent command discovery
		chdir(__DIR__ . '/../fixtures');
	}

	public function testArgs(): void
	{
		$args = Help::args();

		$this->assertArrayHasKey('raw', $args);
		$this->assertSame('raw', $args['raw']['longPrefix']);
		$this->assertSame('Prints only the command names, one per line', $args['raw']['description']);
		$this->assertTrue($args['raw']['noValue']);
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

	public function testCommandRaw(): void
	{
		$lines  = $this->rawOutput($this->createCLI());
		$sorted = $lines;
		sort($sorted);

		// core and custom commands, one name per line
		$this->assertContains('help', $lines);
		$this->assertContains('nested:command', $lines);

		// one list across all groups
		$this->assertSame($sorted, $lines);

		$this->assertOutputNotContains('kirby ');
		$this->assertOutputNotContains('Core commands:');
		$this->assertOutputNotContains('Have fun with the Kirby CLI!');
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

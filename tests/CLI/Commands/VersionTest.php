<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands;

use Kirby\CLI\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Version::class)]
class VersionTest extends TestCase
{
	public function testArgs(): void
	{
		$this->assertSame([], Version::args());
	}

	public function testCommand(): void
	{
		$cli = $this->createCLIWithKirby();
		$this->setupOutputCapture();

		Version::command($cli);

		// Should output the Kirby version (format: x.x.x or x.x.x-xxx)
		$output = $this->getOutput();

		$this->assertMatchesRegularExpression('/\d+\.\d+\.\d+/', $output);
		$this->assertOutputContains($output, $cli->kirby()->version());
	}

	public function testDescription(): void
	{
		$this->assertSame('Prints the Kirby version', Version::description());
	}
}

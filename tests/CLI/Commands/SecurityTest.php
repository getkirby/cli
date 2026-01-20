<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands;

use Kirby\CLI\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Security::class)]
class SecurityTest extends TestCase
{
	public function testArgs(): void
	{
		$this->assertSame([], Security::args());
	}

	public function testCommand(): void
	{
		$cli = $this->createCLIWithKirby();
		$this->setupOutputCapture();

		Security::command($cli);

		// Should output something (either success or security messages)
		$output = $this->getOutput();
		$this->assertNotEmpty($output);
	}

	public function testDescription(): void
	{
		$this->assertSame('Performs security checks of the site', Security::description());
	}
}

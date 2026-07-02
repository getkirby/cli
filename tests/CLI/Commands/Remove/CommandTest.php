<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Remove;

use Kirby\CLI\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Command::class)]
class CommandTest extends TestCase
{
	public function testArgs(): void
	{
		$args = Command::args();

		$this->assertArrayHasKey('name', $args);
		$this->assertSame('The name of the command', $args['name']['description']);
	}

	public function testDescription(): void
	{
		$this->assertSame('Removes a custom command', Command::description());
	}
}

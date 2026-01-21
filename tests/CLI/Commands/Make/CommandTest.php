<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Make;

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

		$this->assertArrayHasKey('global', $args);
		$this->assertSame('Install the command globally', $args['global']['description']);
		$this->assertSame('g', $args['global']['prefix']);
		$this->assertSame('global', $args['global']['longPrefix']);
		$this->assertTrue($args['global']['noValue']);
	}

	public function testDescription(): void
	{
		$this->assertSame('Creates a new local command for the Kirby CLI', Command::description());
	}
}

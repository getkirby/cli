<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\UUID;

use Kirby\CLI\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Generate::class)]
class GenerateTest extends TestCase
{
	public function testArgs(): void
	{
		$this->assertSame([], Generate::args());
	}

	public function testDescription(): void
	{
		$this->assertSame('Creates all missing UUIDs', Generate::description());
	}
}

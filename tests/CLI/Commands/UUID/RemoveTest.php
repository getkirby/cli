<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\UUID;

use Kirby\CLI\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Remove::class)]
class RemoveTest extends TestCase
{
	public function testArgs(): void
	{
		$this->assertSame([], Remove::args());
	}

	public function testDescription(): void
	{
		$this->assertSame('Removes all UUIDs', Remove::description());
	}
}

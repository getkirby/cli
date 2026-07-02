<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\UUID;

use Kirby\CLI\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Populate::class)]
class PopulateTest extends TestCase
{
	public function testArgs(): void
	{
		$this->assertSame([], Populate::args());
	}

	public function testDescription(): void
	{
		$this->assertSame('Populates cache for all UUIDs', Populate::description());
	}
}

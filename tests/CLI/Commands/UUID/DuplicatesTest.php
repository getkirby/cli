<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\UUID;

use Kirby\CLI\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Duplicates::class)]
class DuplicatesTest extends TestCase
{
	public function testArgs(): void
	{
		$args = Duplicates::args();

		$this->assertArrayHasKey('fix', $args);
		$this->assertSame('Fix duplicate UUIDs by generating new ones', $args['fix']['description']);
		$this->assertTrue($args['fix']['noValue']);
	}

	public function testDescription(): void
	{
		$this->assertSame('Find and optionally fix duplicate UUIDs', Duplicates::description());
	}
}

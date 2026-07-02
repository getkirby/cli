<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Clear;

use Kirby\CLI\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Cache::class)]
class CacheTest extends TestCase
{
	public function testArgs(): void
	{
		$args = Cache::args();

		$this->assertArrayHasKey('name', $args);
		$this->assertSame('The name of the cache', $args['name']['description']);
	}

	public function testDescription(): void
	{
		$this->assertSame('Clears the cache', Cache::description());
	}
}

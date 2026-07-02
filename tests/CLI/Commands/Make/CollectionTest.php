<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Make;

use Kirby\CLI\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Collection::class)]
class CollectionTest extends TestCase
{
	public function testArgs(): void
	{
		$args = Collection::args();

		$this->assertArrayHasKey('name', $args);
		$this->assertSame('The name of the collection', $args['name']['description']);
	}

	public function testDescription(): void
	{
		$this->assertSame('Creates a new collection in site/collections', Collection::description());
	}
}

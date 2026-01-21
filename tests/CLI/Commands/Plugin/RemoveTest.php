<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Plugin;

use Kirby\CLI\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Remove::class)]
class RemoveTest extends TestCase
{
	public function testArgs(): void
	{
		$args = Remove::args();

		$this->assertArrayHasKey('repo', $args);
		$this->assertSame('The Kirby plugin registry name (i.e. getkirby/kql)', $args['repo']['description']);
		$this->assertTrue($args['repo']['required']);
	}

	public function testDescription(): void
	{
		$this->assertSame('Removes a Kirby plugin', Remove::description());
	}
}

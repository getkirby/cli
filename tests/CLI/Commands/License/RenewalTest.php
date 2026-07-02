<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\License;

use Kirby\CLI\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Renewal::class)]
class RenewalTest extends TestCase
{
	public function testArgs(): void
	{
		$args = Renewal::args();

		$this->assertArrayHasKey('format', $args);
		$this->assertSame('The format for the renewal date (any format supported by PHP\'s `date()` function) or "timestamp"', $args['format']['description']);
		$this->assertSame('f', $args['format']['prefix']);
		$this->assertSame('format', $args['format']['longPrefix']);
		$this->assertSame('Y-m-d', $args['format']['defaultValue']);
	}

	public function testDescription(): void
	{
		$this->assertSame('Show the renewal date of the activated Kirby license.', Renewal::description());
	}
}

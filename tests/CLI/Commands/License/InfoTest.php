<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\License;

use Kirby\CLI\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Info::class)]
class InfoTest extends TestCase
{
	public function testArgs(): void
	{
		$args = Info::args();

		$this->assertArrayHasKey('format', $args);
		$this->assertSame('Output format: table or json.', $args['format']['description']);
		$this->assertSame('f', $args['format']['prefix']);
		$this->assertSame('format', $args['format']['longPrefix']);
	}

	public function testDescription(): void
	{
		$this->assertSame('Displays Kirby license information in table or JSON format.', Info::description());
	}
}

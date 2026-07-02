<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands;

use Kirby\CLI\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Unzip::class)]
class UnzipTest extends TestCase
{
	public function testArgs(): void
	{
		$args = Unzip::args();

		$this->assertArrayHasKey('file', $args);
		$this->assertSame('The file to unzip', $args['file']['description']);
		$this->assertTrue($args['file']['required']);

		$this->assertArrayHasKey('to', $args);
		$this->assertSame('The place to extract the zip to', $args['to']['description']);
		$this->assertTrue($args['to']['required']);
	}

	public function testDescription(): void
	{
		$this->assertSame('Extracts a zip file', Unzip::description());
	}
}

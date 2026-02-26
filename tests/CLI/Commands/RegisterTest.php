<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands;

use Kirby\CLI\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Register::class)]
class RegisterTest extends TestCase
{
	public function testArgs(): void
	{
		$args = Register::args();

		$this->assertArrayHasKey('email', $args);
		$this->assertSame('e', $args['email']['prefix']);
		$this->assertSame('email', $args['email']['longPrefix']);
		$this->assertSame('The email address you\'ve used to purchase the license', $args['email']['description']);

		$this->assertArrayHasKey('license', $args);
		$this->assertSame('l', $args['license']['prefix']);
		$this->assertSame('license', $args['license']['longPrefix']);
		$this->assertSame('Your Kirby 3 license key', $args['license']['description']);
	}

	public function testDescription(): void
	{
		$this->assertSame('Registers the installation', Register::description());
	}
}

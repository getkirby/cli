<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Make;

use Kirby\CLI\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(User::class)]
class UserTest extends TestCase
{
	public function testArgs(): void
	{
		$args = User::args();

		$this->assertArrayHasKey('email', $args);
		$this->assertSame('The email of the user', $args['email']['description']);

		$this->assertArrayHasKey('role', $args);
		$this->assertSame('The role of the user', $args['role']['description']);

		$this->assertArrayHasKey('name', $args);
		$this->assertSame('The name of the user', $args['name']['description']);

		$this->assertArrayHasKey('language', $args);
		$this->assertSame('The language of the user', $args['language']['description']);

		$this->assertArrayHasKey('password', $args);
		$this->assertSame('The password of the user', $args['password']['description']);
	}

	public function testDescription(): void
	{
		$this->assertSame('Creates a new user', User::description());
	}
}

<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Clear;

use Kirby\CLI\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Logins::class)]
class LoginsTest extends TestCase
{
	public function testArgs(): void
	{
		$this->assertSame([], Logins::args());
	}

	public function testDescription(): void
	{
		$this->assertSame('Deletes the users `.logins` file', Logins::description());
	}
}

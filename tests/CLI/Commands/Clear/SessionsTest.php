<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Clear;

use Kirby\CLI\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Sessions::class)]
class SessionsTest extends TestCase
{
	public function testArgs(): void
	{
		$this->assertSame([], Sessions::args());
	}

	public function testDescription(): void
	{
		$this->assertSame('Destroys all sessions', Sessions::description());
	}
}

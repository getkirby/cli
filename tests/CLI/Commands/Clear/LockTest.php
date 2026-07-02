<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Clear;

use Kirby\CLI\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Lock::class)]
class LockTest extends TestCase
{
	public function testArgs(): void
	{
		$this->assertSame([], Lock::args());
	}

	public function testDescription(): void
	{
		$this->assertSame('Deletes the content `.lock` files', Lock::description());
	}
}

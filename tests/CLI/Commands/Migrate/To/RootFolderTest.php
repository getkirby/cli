<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Migrate\To;

use Kirby\CLI\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RootFolder::class)]
class RootFolderTest extends TestCase
{
	public function testArgs(): void
	{
		$this->assertSame([], RootFolder::args());
	}

	public function testDescription(): void
	{
		$this->assertSame('Switch to a root folder setup', RootFolder::description());
	}
}

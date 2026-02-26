<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Migrate\To;

use Kirby\CLI\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(PublicFolder::class)]
class PublicFolderTest extends TestCase
{
	public function testArgs(): void
	{
		$this->assertSame([], PublicFolder::args());
	}

	public function testDescription(): void
	{
		$this->assertSame('Switch to a public folder setup', PublicFolder::description());
	}
}

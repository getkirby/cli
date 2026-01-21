<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Clear;

use Kirby\CLI\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Media::class)]
class MediaTest extends TestCase
{
	public function testArgs(): void
	{
		$this->assertSame([], Media::args());
	}

	public function testDescription(): void
	{
		$this->assertSame('Deletes the media folder', Media::description());
	}
}

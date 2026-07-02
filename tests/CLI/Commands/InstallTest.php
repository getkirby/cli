<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands;

use Kirby\CLI\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Install::class)]
class InstallTest extends TestCase
{
	public function testArgs(): void
	{
		$args = Install::args();

		$this->assertArrayHasKey('version', $args);
		$this->assertSame('The version corresponding with the tag name in the repo', $args['version']['description']);
		$this->assertSame('latest', $args['version']['defaultValue']);
	}

	public function testDescription(): void
	{
		$this->assertSame('Installs the kirby folder', Install::description());
	}
}

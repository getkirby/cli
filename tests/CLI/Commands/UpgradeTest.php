<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands;

use Kirby\CLI\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Upgrade::class)]
class UpgradeTest extends TestCase
{
	public function testArgs(): void
	{
		$args = Upgrade::args();

		$this->assertArrayHasKey('version', $args);
		$this->assertSame('The version corresponding with tag name in the kirby repo', $args['version']['description']);
		$this->assertSame('latest', $args['version']['defaultValue']);
	}

	public function testDescription(): void
	{
		$this->assertSame('Upgrades the Kirby core', Upgrade::description());
	}
}

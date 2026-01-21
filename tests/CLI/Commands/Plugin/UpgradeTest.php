<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Plugin;

use Kirby\CLI\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Upgrade::class)]
class UpgradeTest extends TestCase
{
	public function testArgs(): void
	{
		$args = Upgrade::args();

		$this->assertArrayHasKey('repo', $args);
		$this->assertSame('The Kirby plugin registry name (i.e. getkirby/kql)', $args['repo']['description']);
		$this->assertTrue($args['repo']['required']);

		$this->assertArrayHasKey('version', $args);
		$this->assertSame('The version corresponding with the tag name in the repo', $args['version']['description']);
		$this->assertSame('latest', $args['version']['defaultValue']);
	}

	public function testDescription(): void
	{
		$this->assertSame('Upgrades a Kirby plugin', Upgrade::description());
	}
}

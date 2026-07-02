<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Make;

use Kirby\CLI\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Config::class)]
class ConfigTest extends TestCase
{
	public function testArgs(): void
	{
		$args = Config::args();

		$this->assertArrayHasKey('domain', $args);
		$this->assertSame('An optional domain for a multi-environment config', $args['domain']['description']);
	}

	public function testDescription(): void
	{
		$this->assertSame('Creates a new config file in site/config', Config::description());
	}
}

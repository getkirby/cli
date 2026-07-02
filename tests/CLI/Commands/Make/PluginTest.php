<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Make;

use Kirby\CLI\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Plugin::class)]
class PluginTest extends TestCase
{
	public function testArgs(): void
	{
		$args = Plugin::args();

		$this->assertArrayHasKey('name', $args);
		$this->assertSame('The name of the plugin (`vendor/plugin`)', $args['name']['description']);
	}

	public function testDescription(): void
	{
		$this->assertSame('Creates a new plugin in site/plugins', Plugin::description());
	}
}

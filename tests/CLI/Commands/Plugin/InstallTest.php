<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Plugin;

use Kirby\CLI\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Install::class)]
class InstallTest extends TestCase
{
	public function testArgs(): void
	{
		$args = Install::args();

		$this->assertArrayHasKey('repo', $args);
		$this->assertSame('The Github repo path (i.e. getkirby/kql)', $args['repo']['description']);
		$this->assertTrue($args['repo']['required']);

		$this->assertArrayHasKey('version', $args);
		$this->assertSame('The version corresponding with the tag name in the repo', $args['version']['description']);
		$this->assertSame('latest', $args['version']['defaultValue']);
	}

	public function testDescription(): void
	{
		$this->assertSame('Installs a Kirby plugin repository from Github', Install::description());
	}
}

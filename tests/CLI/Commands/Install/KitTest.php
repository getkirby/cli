<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Install;

use Kirby\CLI\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Kit::class)]
class KitTest extends TestCase
{
	public function testArgs(): void
	{
		$args = Kit::args();

		$this->assertArrayHasKey('kit', $args);
		$this->assertSame('The name of the kit (starterkit, demokit, plainkit)', $args['kit']['description']);

		$this->assertArrayHasKey('folder', $args);
		$this->assertSame('The name of folder the kit should be installed into', $args['folder']['description']);
	}

	public function testDescription(): void
	{
		$this->assertSame('Installs a Kirby Kit in a subfolder', Kit::description());
	}
}

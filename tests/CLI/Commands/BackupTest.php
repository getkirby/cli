<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands;

use Kirby\CLI\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Backup::class)]
class BackupTest extends TestCase
{
	public function testArgs(): void
	{
		$args = Backup::args();

		$this->assertArrayHasKey('root', $args);
		$this->assertSame('Selects the kirby root, which should be backuped', $args['root']['description']);
	}

	public function testDescription(): void
	{
		$this->assertSame('Creates backup of application files', Backup::description());
	}
}

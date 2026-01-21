<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Install;

use Kirby\CLI\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Repo::class)]
class RepoTest extends TestCase
{
	public function testArgs(): void
	{
		$args = Repo::args();

		$this->assertArrayHasKey('repo', $args);
		$this->assertSame('The Github repo path (i.e. getkirby/kirby)', $args['repo']['description']);
		$this->assertTrue($args['repo']['required']);

		$this->assertArrayHasKey('folder', $args);
		$this->assertSame('The name of folder the repo should be installed into', $args['folder']['description']);

		$this->assertArrayHasKey('version', $args);
		$this->assertSame('The version corresponding with the tag name in the repo', $args['version']['description']);
		$this->assertSame('v', $args['version']['prefix']);
		$this->assertSame('version', $args['version']['longPrefix']);
		$this->assertSame('latest', $args['version']['defaultValue']);
	}

	public function testDescription(): void
	{
		$this->assertSame('Downloads a repository from the getkirby org on Github', Repo::description());
	}
}

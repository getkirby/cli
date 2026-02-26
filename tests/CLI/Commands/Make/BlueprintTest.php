<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Make;

use Kirby\CLI\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Blueprint::class)]
class BlueprintTest extends TestCase
{
	public function testArgs(): void
	{
		$args = Blueprint::args();

		$this->assertArrayHasKey('name', $args);
		$this->assertSame('The name of the blueprint', $args['name']['description']);
	}

	public function testDescription(): void
	{
		$this->assertSame('Creates a new blueprint file in site/blueprints', Blueprint::description());
	}
}

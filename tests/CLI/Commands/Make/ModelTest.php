<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Make;

use Kirby\CLI\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Model::class)]
class ModelTest extends TestCase
{
	public function testArgs(): void
	{
		$args = Model::args();

		$this->assertArrayHasKey('name', $args);
		$this->assertSame('The name of the model', $args['name']['description']);
	}

	public function testDescription(): void
	{
		$this->assertSame('Creates a new page model in site/models', Model::description());
	}
}

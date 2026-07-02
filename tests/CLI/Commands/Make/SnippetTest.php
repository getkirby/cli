<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Make;

use Kirby\CLI\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Snippet::class)]
class SnippetTest extends TestCase
{
	public function testArgs(): void
	{
		$args = Snippet::args();

		$this->assertArrayHasKey('name', $args);
		$this->assertSame('The name of the snippet', $args['name']['description']);
	}

	public function testDescription(): void
	{
		$this->assertSame('Creates a new snippet in site/snippets', Snippet::description());
	}
}

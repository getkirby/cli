<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Make;

use Kirby\CLI\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Template::class)]
class TemplateTest extends TestCase
{
	public function testArgs(): void
	{
		$args = Template::args();

		$this->assertArrayHasKey('name', $args);
		$this->assertSame('The name of the template', $args['name']['description']);
	}

	public function testDescription(): void
	{
		$this->assertSame('Creates a new template in site/templates', Template::description());
	}
}

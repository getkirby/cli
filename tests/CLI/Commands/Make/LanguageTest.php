<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Make;

use Kirby\CLI\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Language::class)]
class LanguageTest extends TestCase
{
	public function testArgs(): void
	{
		$args = Language::args();

		$this->assertArrayHasKey('code', $args);
		$this->assertSame('The code of the language', $args['code']['description']);

		$this->assertArrayHasKey('name', $args);
		$this->assertSame('The name of the language', $args['name']['description']);

		$this->assertArrayHasKey('locale', $args);
		$this->assertSame('The locale of the language', $args['locale']['description']);

		$this->assertArrayHasKey('direction', $args);
		$this->assertSame('The direction of the language', $args['direction']['description']);
	}

	public function testDescription(): void
	{
		$this->assertSame('Creates a new language', Language::description());
	}
}

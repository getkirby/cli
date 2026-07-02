<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Clean;

use Kirby\CLI\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Content::class)]
class ContentTest extends TestCase
{
	public function testArgs(): void
	{
		$args = Content::args();

		$this->assertArrayHasKey('dry-run', $args);
		$this->assertSame('Run the command without actually updating content', $args['dry-run']['description']);
		$this->assertTrue($args['dry-run']['noValue']);
	}

	public function testDescription(): void
	{
		$this->assertSame('Deletes all fields from page, file or user content files that are not defined in the blueprint, no matter if they contain content or not.', Content::description());
	}
}

<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands;

use Kirby\CLI\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Download::class)]
class DownloadTest extends TestCase
{
	public function testArgs(): void
	{
		$args = Download::args();

		$this->assertArrayHasKey('url', $args);
		$this->assertSame('The URL to the file', $args['url']['description']);
		$this->assertTrue($args['url']['required']);

		$this->assertArrayHasKey('file', $args);
		$this->assertSame('Where to save the download', $args['file']['description']);
		$this->assertTrue($args['file']['required']);
	}

	public function testDescription(): void
	{
		$this->assertSame('Downloads a file via URL', Download::description());
	}
}

<?php

declare(strict_types = 1);

namespace Kirby\CLI;

use PHPUnit\Framework\Attributes\CoversFunction;

#[CoversFunction('bootstrap')]
#[CoversFunction('index')]
class BootstrapTest extends TestCase
{
	public function testIndexInPublic(): void
	{
		chdir($root = __DIR__ . '/fixtures/bootstrap/c');
		$this->assertSame($root . '/public/index.php', index());
		$this->assertSame($root . '/public/index.php', bootstrap());
	}

	public function testIndexInPublicHtml(): void
	{
		chdir($root = __DIR__ . '/fixtures/bootstrap/d');
		$this->assertSame($root . '/public_html/index.php', index());
		$this->assertSame($root . '/public_html/index.php', bootstrap());
	}

	public function testIndexInRoot(): void
	{
		chdir($root = __DIR__ . '/fixtures/bootstrap/a');
		$this->assertSame($root . '/index.php', index());
		$this->assertSame($root . '/index.php', bootstrap());
	}

	public function testIndexInWww(): void
	{
		chdir($root = __DIR__ . '/fixtures/bootstrap/b');
		$this->assertSame($root . '/www/index.php', index());
		$this->assertSame($root . '/www/index.php', bootstrap());
	}
}

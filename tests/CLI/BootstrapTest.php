<?php

namespace Kirby\CLI;

class BootstrapTest extends TestCase
{
	/**
	 * @covers bootstrap
	 * @covers index
	 */
	public function testIndexInRoot()
	{
		chdir($root = __DIR__ . '/bootstrap/a');
		$this->assertSame($root . '/index.php', index());
		$this->assertSame($root . '/index.php', bootstrap());
	}

	/**
	 * @covers bootstrap
	 * @covers index
	 */
	public function testIndexInWww()
	{
		chdir($root = __DIR__ . '/bootstrap/b');
		$this->assertSame($root . '/www/index.php', index());
		$this->assertSame($root . '/www/index.php', bootstrap());
	}

	/**
	 * @covers bootstrap
	 * @covers index
	 */
	public function testIndexInPublic()
	{
		chdir($root = __DIR__ . '/bootstrap/c');
		$this->assertSame($root . '/public/index.php', index());
		$this->assertSame($root . '/public/index.php', bootstrap());
	}

	/**
	 * @covers bootstrap
	 * @covers index
	 */
	public function testIndexInPublicHtml()
	{
		chdir($root = __DIR__ . '/bootstrap/d');
		$this->assertSame($root . '/public_html/index.php', index());
		$this->assertSame($root . '/public_html/index.php', bootstrap());
	}
}

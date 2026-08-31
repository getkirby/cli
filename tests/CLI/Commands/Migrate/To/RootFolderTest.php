<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Migrate\To;

use Kirby\CLI\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(RootFolder::class)]
class RootFolderTest extends TestCase
{
	protected string|null $cwd = null;

	public static function startCommandProvider(): array
	{
		return [
			// removes the document root from the start script
			[
				'@php -S localhost:8000 -t public kirby/router.php',
				'@php -S localhost:8000 kirby/router.php'
			],
			// removes any other document root as well
			[
				'@php -S localhost:8000 -t public_html kirby/router.php',
				'@php -S localhost:8000 kirby/router.php'
			],
			// no matter where it sits in the command
			[
				'@php -t public -S localhost:8000 kirby/router.php',
				'@php -S localhost:8000 kirby/router.php'
			],
			// keeps a start script that is already correct
			[
				'@php -S localhost:8000 kirby/router.php',
				'@php -S localhost:8000 kirby/router.php'
			],
			// commands that start no server are left alone
			[
				'Composer\Config::disableProcessTimeout',
				null
			],
		];
	}

	public function testArgs(): void
	{
		$this->assertSame([], RootFolder::args());
	}

	public function testDescription(): void
	{
		$this->assertSame('Switch to a root folder setup', RootFolder::description());
	}

	public function testDocumentRoot(): void
	{
		$cli = $this->createCLI();
		$this->assertNull(RootFolderProxy::documentRoot($cli));
	}

	#[DataProvider('startCommandProvider')]
	public function testUpdateStartCommand(string $command, string|null $expected): void
	{
		$this->assertSame($expected, RootFolderProxy::updateStartCommand($command, null));
	}

	public function testUpdateComposerConfig(): void
	{
		$cli = $this->createCLIWithKirby();
		$this->setupOutputCapture();

		chdir($root = $this->kirbyRoot());

		$before = implode("\n", [
			'{',
			'    "extra": {},',
			'    "scripts": {',
			'        "start": [',
			'            "Composer\\\\Config::disableProcessTimeout",',
			'            "@php -S localhost:8000 -t public_html kirby/router.php"',
			'        ]',
			'    }',
			'}',
			''
		]);

		file_put_contents($root . '/composer.json', $before);

		RootFolderProxy::updateComposerConfig($cli);

		$composer = json_decode($after = file_get_contents($root . '/composer.json'), true);

		$this->assertSame(
			'@php -S localhost:8000 kirby/router.php',
			$composer['scripts']['start'][1]
		);

		// empty objects survive the round trip
		$this->assertStringContainsString('"extra": {}', $after);

		$this->assertOutputContains('The composer.json has been updated');
	}

	protected function setUp(): void
	{
		$this->cwd = getcwd();
	}

	protected function tearDown(): void
	{
		if ($this->cwd !== null) {
			chdir($this->cwd);
			$this->cwd = null;
		}

		parent::tearDown();
	}
}

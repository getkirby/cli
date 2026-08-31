<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Migrate\To;

use Kirby\CLI\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

#[CoversClass(PublicFolder::class)]
class PublicFolderTest extends TestCase
{
	protected string|null $cwd = null;

	public static function startCommandProvider(): array
	{
		return [
			// adds the document root to the start script of the kits
			[
				'@php -S localhost:8000 kirby/router.php',
				'@php -S localhost:8000 -t public kirby/router.php'
			],
			// keeps a start script that is already correct
			[
				'@php -S localhost:8000 -t public kirby/router.php',
				'@php -S localhost:8000 -t public kirby/router.php'
			],
			// replaces a different document root
			[
				'@php -S localhost:8000 -t public_html kirby/router.php',
				'@php -S localhost:8000 -t public kirby/router.php'
			],
			// moves the document root behind the host and port
			[
				'@php -t public_html -S localhost:8000 kirby/router.php',
				'@php -S localhost:8000 -t public kirby/router.php'
			],
			// works with any host and port
			[
				'@php -S 0.0.0.0:3000 kirby/router.php',
				'@php -S 0.0.0.0:3000 -t public kirby/router.php'
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
		$this->assertSame([], PublicFolder::args());
	}

	public function testDescription(): void
	{
		$this->assertSame('Switch to a public folder setup', PublicFolder::description());
	}

	public function testDocumentRoot(): void
	{
		$cli = $this->createCLI();
		$this->assertSame('public', PublicFolderProxy::documentRoot($cli));
	}

	#[DataProvider('startCommandProvider')]
	public function testUpdateStartCommand(string $command, string|null $expected): void
	{
		$this->assertSame($expected, PublicFolderProxy::updateStartCommand($command, 'public'));
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
			'            "@php -S localhost:8000 kirby/router.php"',
			'        ]',
			'    }',
			'}',
			''
		]);

		file_put_contents($root . '/composer.json', $before);

		PublicFolderProxy::updateComposerConfig($cli);

		$after = file_get_contents($root . '/composer.json');

		$this->assertStringContainsString('-S localhost:8000 -t public', $after);

		// empty objects survive the round trip
		$this->assertStringContainsString('"extra": {}', $after);

		// so does the trailing newline
		$this->assertStringEndsWith("\n", $after);

		$this->assertOutputContains('The composer.json has been updated');
	}

	public function testUpdateComposerConfigWithStringScript(): void
	{
		$cli = $this->createCLIWithKirby();
		$this->setupOutputCapture();

		chdir($root = $this->kirbyRoot());

		$before = implode("\n", [
			'{',
			'    "scripts": {',
			'        "start": "@php -S localhost:8000 kirby/router.php"',
			'    }',
			'}',
			''
		]);

		file_put_contents($root . '/composer.json', $before);

		PublicFolderProxy::updateComposerConfig($cli);

		$composer = json_decode(file_get_contents($root . '/composer.json'), true);

		$this->assertSame(
			'@php -S localhost:8000 -t public kirby/router.php',
			$composer['scripts']['start']
		);
	}

	public function testUpdateComposerConfigWithoutChanges(): void
	{
		$cli = $this->createCLIWithKirby();
		$this->setupOutputCapture();

		chdir($root = $this->kirbyRoot());

		$before = implode("\n", [
			'{',
			'  "extra": {},',
			'  "scripts": {',
			'    "start": "@php -S localhost:8000 -t public kirby/router.php"',
			'  }',
			'}',
			''
		]);

		file_put_contents($root . '/composer.json', $before);

		PublicFolderProxy::updateComposerConfig($cli);

		// the file keeps its own formatting because it is never written
		$this->assertSame($before, file_get_contents($root . '/composer.json'));
		$this->assertOutputNotContains('The composer.json has been updated');
	}

	public function testUpdateComposerConfigWithoutServerCommand(): void
	{
		$cli = $this->createCLIWithKirby();
		$this->setupOutputCapture();

		chdir($root = $this->kirbyRoot());

		$before = implode("\n", [
			'{',
			'    "scripts": {',
			'        "start": "vite dev"',
			'    }',
			'}',
			''
		]);

		file_put_contents($root . '/composer.json', $before);

		PublicFolderProxy::updateComposerConfig($cli);

		$this->assertSame($before, file_get_contents($root . '/composer.json'));
		$this->assertOutputContains('Please set the document root manually');
	}

	public function testUpdateComposerConfigWithInvalidJson(): void
	{
		$cli = $this->createCLIWithKirby();
		$this->setupOutputCapture();

		chdir($root = $this->kirbyRoot());

		file_put_contents($root . '/composer.json', '{');

		PublicFolderProxy::updateComposerConfig($cli);

		$this->assertOutputContains('The composer.json could not be parsed');
	}

	public function testUpdateComposerConfigWithoutFile(): void
	{
		$cli = $this->createCLI();
		$this->setupOutputCapture();

		// there is no composer.json in the test directory
		chdir(__DIR__);

		PublicFolderProxy::updateComposerConfig($cli);

		$this->assertSame('', $this->getOutput());
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

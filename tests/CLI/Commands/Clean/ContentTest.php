<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Clean;

use Kirby\CLI\TestableCLI;
use Kirby\CLI\TestCase;
use Kirby\Cms\Languages;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionMethod;

#[CoversClass(Content::class)]
class ContentTest extends TestCase
{
	protected const CONTENT = "Title: Test\n\n----\n\nText: Hello\n\n----\n\nRemoved: Old value\n";

	protected function call(string $method, mixed ...$args): mixed
	{
		return (new ReflectionMethod(Content::class, $method))->invoke(null, ...$args);
	}

	protected function cleanTestPage(TestableCLI $cli): void
	{
		$this->call(
			'cleanItem',
			$cli,
			$cli->kirby()->page('test'),
			Languages::ensure()->first()->code(),
			['title'],
			false
		);
	}

	protected function createCLIWithTestPage(array $content = []): TestableCLI
	{
		$cli = $this->createCLIWithKirby(content: [
			'test/test.txt' => static::CONTENT,
			...$content
		]);

		$dir = $this->kirbyRoot() . '/site/blueprints/pages';
		mkdir($dir, 0755, true);
		file_put_contents($dir . '/test.yml', "title: Test\nfields:\n  text:\n    type: text\n");

		$this->setupOutputCapture($cli);

		return $cli;
	}

	protected function pageContent(): string
	{
		return file_get_contents($this->kirbyRoot() . '/content/test/test.txt');
	}

	public function testArgs(): void
	{
		$args = Content::args();

		$this->assertArrayHasKey('dry-run', $args);
		$this->assertSame('Run the command without actually updating content', $args['dry-run']['description']);
		$this->assertTrue($args['dry-run']['noValue']);
	}

	public function testCleanItem(): void
	{
		$cli = $this->createCLIWithTestPage();

		$this->cleanTestPage($cli);

		$this->assertOutputContains('Remove "removed" from page (test)');
		$this->assertStringContainsString('Text: Hello', $this->pageContent());
		$this->assertStringNotContainsString('Removed: Old value', $this->pageContent());
	}

	public function testCleanItemWithChanges(): void
	{
		$cli = $this->createCLIWithTestPage([
			'test/_changes/test.txt' => static::CONTENT
		]);

		$this->cleanTestPage($cli);

		$this->assertOutputContains('The page (test) has changes and cannot be cleaned');
		$this->assertStringContainsString('Removed: Old value', $this->pageContent());
	}

	public function testDescription(): void
	{
		$this->assertSame('Deletes all fields from page, file or user content files that are not defined in the blueprint, no matter if they contain content or not.', Content::description());
	}
}

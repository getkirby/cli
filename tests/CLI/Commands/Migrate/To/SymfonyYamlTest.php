<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Migrate\To;

use ErrorException;
use Exception;
use Kirby\CLI\TestableCLI;
use Kirby\CLI\TestCase;
use Kirby\Data\YamlSpyc;
use Kirby\Data\YamlSymfony;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionMethod;

#[CoversClass(SymfonyYaml::class)]
class SymfonyYamlTest extends TestCase
{
	protected const EVENTS = "Title: Events\n\n----\n\nDates:\n\n- \n  date: 2024-01-01\n  title: Opening\n- \n  date: 2024-02-01\n  title: Closing\n";
	protected const TEAM   = "Title: Team\n\n----\n\nSocial:\n\n- \n  handle: @kirby\n";

	protected function blueprint(string $name, string $yaml): void
	{
		$dir = $this->kirbyRoot() . '/site/blueprints/pages';
		@mkdir($dir, 0755, true);
		file_put_contents($dir . '/' . $name . '.yml', $yaml);
	}

	protected function call(string $method, mixed ...$args): mixed
	{
		return (new ReflectionMethod(SymfonyYaml::class, $method))->invoke(null, ...$args);
	}

	protected function createCLIWithIssues(
		bool $errors = true,
		array $options = []
	): TestableCLI {
		$cli = $this->createCLIWithKirby(content: [
			'yaml-events/yaml-events.txt' => static::EVENTS,
			'yaml-team/yaml-team.txt'     => static::TEAM,
		]);

		$placeholder = $errors === true ? '@yourhandle' : '"@yourhandle"';

		$this->blueprint('yaml-events', "title: Events\nfields:\n  featured:\n    type: toggle\n    translate: no\n  dates:\n    type: structure\n    fields:\n      date:\n        type: date\n      title:\n        type: text\n");
		$this->blueprint('yaml-team', "title: Team\nfields:\n  social:\n    type: structure\n    fields:\n      handle:\n        type: text\n        placeholder: " . $placeholder . "\n");

		if ($options !== []) {
			$cli = $this->createCLI($cli->kirby()->clone(['options' => $options]));

			// the cloned app registers its own error handlers
			restore_error_handler();
			restore_exception_handler();
		}

		$cli->climate()->arguments->add(SymfonyYaml::args());
		$cli->climate()->arguments->parse(['kirby', '--dry-run']);

		return $cli;
	}

	public function testArgs(): void
	{
		$args = SymfonyYaml::args();

		$this->assertArrayHasKey('dry-run', $args);
		$this->assertSame('List the issues without converting any content', $args['dry-run']['description']);
		$this->assertTrue($args['dry-run']['noValue']);
	}

	public function testBlueprintFiles(): void
	{
		$kirby   = $this->kirby();
		$plugin  = $this->kirbyRoot() . '/site/plugins/demo/blueprints';
		$pages   = $this->kirbyRoot() . '/site/blueprints/pages';

		mkdir($plugin, 0755, true);

		$this->blueprint('yaml-site', 'title: Site');

		foreach (['callback', 'registered', 'replaced', 'unregistered'] as $name) {
			file_put_contents($plugin . '/' . $name . '.yml', 'title: ' . $name);
		}

		// Kirby only reads `.yml` files from the blueprints folder
		file_put_contents($pages . '/ignored.yaml', 'title: Ignored');

		$kirby = $kirby->clone([
			'blueprints' => [
				'pages/array'      => ['title' => 'Array'],
				'pages/callback'   => fn () => $plugin . '/callback.yml',
				'pages/registered' => $plugin . '/registered.yml',
				'pages/yaml-site'  => $plugin . '/replaced.yml',
			]
		]);

		// the cloned app registers its own error handlers
		restore_error_handler();
		restore_exception_handler();

		$this->assertSame([
			realpath($pages . '/yaml-site.yml'),
			realpath($plugin . '/callback.yml'),
			realpath($plugin . '/registered.yml'),
		], $this->call('blueprintFiles', $kirby));
	}

	public function testCheckOtherFields(): void
	{
		$cli = $this->createCLIWithKirby(content: [
			'yaml-other/yaml-other.txt' => "Title: Release: 2024-01-01\n\n----\n\nText:\n\n- 2024-01-01\n\n----\n\nPlugin:\n\n- 2024-02-01\n\n----\n\nMeta:\n\n- \n  date: 2024-03-01\n",
		]);

		$this->blueprint('yaml-other', "title: Other\nfields:\n  text:\n    type: textarea\n  plugin:\n    type: entrylist\n");

		// a plugin field type that stores YAML
		$kirby = $cli->kirby()->clone([
			'fields' => [
				'entrylist' => $cli->kirby()->core()->fields()['entries']
			]
		]);

		// the cloned app registers its own error handlers
		restore_error_handler();
		restore_exception_handler();

		$cli = $this->createCLI($kirby);
		$this->setupOutputCapture();

		$this->assertSame(1, $this->call('checkOtherFields', $cli, $kirby));

		$this->assertOutputContains('content/yaml-other/yaml-other.txt');
		$this->assertOutputContains('  plugin.0: "2024-02-01" → 1706745600');
		$this->assertOutputContains('  meta.0.date: "2024-03-01" → 1709251200');
		$this->assertOutputContains('Check them by hand');

		// core fields and plain text are skipped
		$this->assertOutputNotContains('text.0');
		$this->assertOutputNotContains('title');
	}

	public function testCommand(): void
	{
		$cli = $this->createCLIWithKirby();
		$this->setupOutputCapture();

		SymfonyYaml::command($cli);

		$this->assertOutputContains('All blueprints are ready for Symfony YAML');
		$this->assertOutputContains('All content is ready for Symfony YAML');
		$this->assertOutputContains('Your site is ready for Symfony YAML');
	}

	public function testCommandWithDryRun(): void
	{
		$cli = $this->createCLIWithIssues(
			errors: false,
			options: ['yaml.handler' => 'symfony']
		);
		$this->setupOutputCapture();

		try {
			SymfonyYaml::command($cli);
			$this->fail('The command should fail');
		} catch (Exception $e) {
			$this->assertSame('The migration to Symfony YAML is not complete yet', $e->getMessage());
		}

		$this->assertOutputNotContains('site/blueprints/pages/yaml-team.yml');
		$this->assertOutputContains('content/yaml-team/yaml-team.txt');
		$this->assertOutputContains('Run the command without --dry-run to convert these content files');

		// nothing has been converted
		$this->assertSame(static::TEAM, file_get_contents($this->kirbyRoot() . '/content/yaml-team/yaml-team.txt'));
	}

	public function testCommandWithIssues(): void
	{
		$cli = $this->createCLIWithIssues();
		$this->setupOutputCapture();

		try {
			SymfonyYaml::command($cli);
			$this->fail('The command should fail');
		} catch (Exception $e) {
			$this->assertSame('The migration to Symfony YAML is not complete yet', $e->getMessage());
		}

		// blueprints
		$this->assertOutputContains('site/blueprints/pages/yaml-events.yml');
		$this->assertOutputContains('  fields.featured.translate: false → "no"');
		$this->assertOutputContains('site/blueprints/pages/yaml-team.yml');
		$this->assertOutputContains('  The reserved indicator "@" cannot start a plain scalar');

		// content
		$this->assertOutputContains('content/yaml-events/yaml-events.txt');
		$this->assertOutputContains('  dates.0.date: "2024-01-01" → 1704067200');
		$this->assertOutputContains('  dates.1.date: "2024-02-01" → 1706745600');
		$this->assertOutputContains('content/yaml-team/yaml-team.txt');
		$this->assertOutputContains('  social: The reserved indicator "@" cannot start a plain scalar');

		// Spyc is the default handler in Kirby 5; with Symfony YAML,
		// Kirby cannot load the blueprint with the error
		$this->assertOutputContains(
			$this->call('readsSpyc') === true
				? 'Kirby still reads YAML with Spyc'
				: 'Fix the blueprints first'
		);

		// nothing has been converted
		$this->assertSame(static::EVENTS, file_get_contents($this->kirbyRoot() . '/content/yaml-events/yaml-events.txt'));
	}

	public function testCommandWithOtherFields(): void
	{
		$cli = $this->createCLIWithKirby(content: [
			'yaml-meta/yaml-meta.txt' => "Title: Meta\n\n----\n\nMeta:\n\n- \n  date: 2024-03-01\n",
		]);
		$this->setupOutputCapture();

		SymfonyYaml::command($cli);

		$this->assertOutputContains('All content is ready for Symfony YAML');
		$this->assertOutputContains('  meta.0.date: "2024-03-01" → 1709251200');
		$this->assertOutputContains('Check them by hand');
		$this->assertOutputNotContains('Your site is ready for Symfony YAML');
	}

	public function testConvertContent(): void
	{
		$cli = $this->createCLIWithIssues();
		$this->setupOutputCapture();

		$files = $this->call('checkContent', $cli, $cli->kirby());
		$this->assertCount(2, $files);

		$failed = $this->call('convertContent', $cli, $files);
		$this->assertSame([], $failed);
		$this->assertMatchesRegularExpression('!✅ .*/content/yaml-events/yaml-events\.txt!', $this->getOutput());
		$this->assertMatchesRegularExpression('!✅ .*/content/yaml-team/yaml-team\.txt!', $this->getOutput());

		$events = $cli->kirby()->page('yaml-events')->version()->read();
		$team   = $cli->kirby()->page('yaml-team')->version()->read();

		// Symfony YAML reads the content like Spyc read the original
		$this->assertSame(YamlSpyc::decode("- \n  date: 2024-01-01\n  title: Opening\n- \n  date: 2024-02-01\n  title: Closing"), YamlSymfony::decode($events['dates']));
		$this->assertSame([['handle' => '@kirby']], YamlSymfony::decode($team['social']));
		$this->assertStringContainsString("date: '2024-01-01'", $events['dates']);
		$this->assertSame('Events', $events['title']);

		// the converted content does not need to be converted again
		$this->assertSame([], $this->call('checkContent', $cli, $cli->kirby()));
	}

	public function testConvertContentWithSymfonySyntax(): void
	{
		// Symfony YAML cannot read the handle, Spyc misreads the text
		$content = "Title: Notes\n\n----\n\nNotes:\n\n-\n  text: |-\n    line 1\n    line 2\n  handle: @kirby\n";

		$cli = $this->createCLIWithKirby(content: [
			'yaml-notes/yaml-notes.txt' => $content,
		]);

		$this->blueprint('yaml-notes', "title: Notes\nfields:\n  notes:\n    type: structure\n    fields:\n      text:\n        type: textarea\n      handle:\n        type: text\n");
		$this->setupOutputCapture();

		$files = $this->call('checkContent', $cli, $cli->kirby());
		$this->assertCount(1, $files);

		$failed = $this->call('convertContent', $cli, $files);
		$this->assertSame($files, $failed);
		$this->assertOutputContains('The "notes" field contains YAML that only Symfony YAML can read');

		// nothing has been converted
		$this->assertSame($content, file_get_contents($this->kirbyRoot() . '/content/yaml-notes/yaml-notes.txt'));
	}

	public function testDescription(): void
	{
		$this->assertSame('Checks blueprints and converts content from Spyc to Symfony YAML', SymfonyYaml::description());
	}

	public function testDiffWithBooleans(): void
	{
		$this->kirby();

		$yaml = "fields:\n  hasHeadline:\n    type: radio\n    options:\n      - yes\n      - no\n  headline:\n    when:\n      hasHeadline: yes\n";

		$this->assertSame([
			'fields.hasHeadline.options.0: true → "yes"',
			'fields.hasHeadline.options.1: false → "no"',
			'fields.headline.when.hasHeadline: true → "yes"',
		], $this->call('diff', $yaml));
	}

	public function testIsSpycArray(): void
	{
		$this->kirby();

		$this->assertTrue($this->call('isSpycArray', "- \n  date: 2024-01-01"));
		$this->assertTrue($this->call('isSpycArray', '- page://abc'));

		// plain text
		$this->assertFalse($this->call('isSpycArray', 'Kirby: The CMS'));
		$this->assertFalse($this->call('isSpycArray', "# Heading\n\nSome text: here"));

		// Spyc emits warnings for some plain text, which
		// Kirby's error handler turns into exceptions
		set_error_handler(fn (int $level, string $message) => throw new ErrorException($message));

		try {
			$this->assertFalse($this->call('isSpycArray', "| a | b |\n|---|---|\n| 1 | 2 |"));
		} finally {
			restore_error_handler();
		}
	}

	public function testNeedsConversion(): void
	{
		$this->kirby();

		// Spyc YAML that Symfony YAML reads differently
		$this->assertSame(
			['dates.0.date: "2024-01-01" → 1704067200'],
			$this->call('needsConversion', "- \n  date: 2024-01-01", 'dates')
		);

		// Spyc YAML that Symfony YAML cannot read
		$this->assertStringStartsWith(
			'social: The reserved indicator "@"',
			$this->call('needsConversion', "- \n  handle: @kirby", 'social')[0]
		);

		// YAML that both read the same way
		$this->assertSame([], $this->call('needsConversion', "- \n  title: Opening", 'dates'));

		// a line in a multi-line string that only looks like a block scalar
		$this->assertSame(
			['dates.0.date: "2024-01-01" → 1704067200'],
			$this->call('needsConversion', "- \n  date: 2024-01-01\n  text: |\n    Nice place\n    Rating: >4", 'dates')
		);
	}

	public function testNeedsConversionWithSymfonyYaml(): void
	{
		$this->kirby();

		// Spyc misreads multi-line strings written by Symfony YAML
		$yaml = YamlSymfony::encode([['text' => "line 1\nline 2"]]);

		$this->assertNotSame([], $this->call('diff', $yaml, 'text'));
		$this->assertSame([], $this->call('needsConversion', $yaml, 'text'));

		// a hand edit keeps the YAML from matching Symfony YAML's output
		$yaml = YamlSymfony::encode([['text' => "line 1\nline 2", 'quote' => "it's"]]);
		$yaml = str_replace('quote: "it\'s"', "quote: it's", $yaml);

		$this->assertNotSame(trim($yaml), trim(YamlSymfony::encode(YamlSymfony::decode($yaml))));
		$this->assertSame([], $this->call('needsConversion', $yaml, 'text'));
	}

	public function testPath(): void
	{
		$cli  = $this->createCLI();
		$file = $cli->dir() . DIRECTORY_SEPARATOR . 'site' . DIRECTORY_SEPARATOR . 'blueprints/site.yml';

		$this->assertSame('site/blueprints/site.yml', $this->call('path', $cli, $file));
		$this->assertSame('/var/www/site.yml', $this->call('path', $cli, '/var/www/site.yml'));
	}

	public function testUsesSymfonySyntax(): void
	{
		$this->kirby();

		// block scalars that Symfony YAML writes
		foreach (["a\nb", "a\nb\n\n", "  a\nb", "  a\nb\n", "\na\nb"] as $text) {
			$yaml = YamlSymfony::encode([['text' => $text]]);
			$this->assertTrue($this->call('usesSymfonySyntax', $yaml), $yaml);
		}

		$this->assertTrue($this->call('usesSymfonySyntax', "- >-\n  a\n  b"));

		// block scalars that Spyc can read
		$this->assertFalse($this->call('usesSymfonySyntax', YamlSpyc::encode([['text' => "a\nb"]])));
		$this->assertFalse($this->call('usesSymfonySyntax', "text: >\n  a\n  b"));
		$this->assertFalse($this->call('usesSymfonySyntax', 'title: a |- b'));

		// lines in multi-line strings that only look like block scalars
		$this->assertFalse($this->call('usesSymfonySyntax', "text: |\n  Nice place\n  Rating: >4"));
		$this->assertFalse($this->call('usesSymfonySyntax', "text: |\n  Rating: >4\n  Nice place"));
		$this->assertFalse($this->call('usesSymfonySyntax', "- \n  sign: |-\n  date: 2024-01-01"));
	}
}

<?php

declare(strict_types = 1);

namespace Kirby\CLI;

use Exception;
use League\CLImate\CLImate;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CLI::class)]
class CLITest extends TestCase
{
	/**
	 * Runs the CLI binary in a separate process,
	 * because a failing command ends the process
	 * and its exit status is what we need to check
	 */
	protected function runBinary(string ...$args): array
	{
		$process = proc_open(
			[PHP_BINARY, dirname(__DIR__, 2) . '/bin/kirby', ...$args],
			[1 => ['pipe', 'w'], 2 => ['redirect', 1]],
			$pipes,
			__DIR__ . '/fixtures'
		);

		$output = stream_get_contents($pipes[1]);
		fclose($pipes[1]);

		return [proc_close($process), $output];
	}

	protected function setUp(): void
	{
		chdir(__DIR__ . '/fixtures');
	}

	public function testClimate(): void
	{
		$cli = new CLI();
		$this->assertInstanceOf(CLImate::class, $cli->climate());
	}

	public function testCommand(): void
	{
		[$status] = $this->runBinary('test');
		$this->assertSame(0, $status);
	}

	public function testCommandsInDirectory(): void
	{
		$cli = new CLI();

		// missing command directory
		$commands = $cli->commandsInDirectory(__DIR__ . '/does-not-exist');
		$this->assertSame([], $commands);

		// existing command directory
		$commands = $cli->commandsInDirectory(__DIR__ . '/fixtures/commands');
		$expected = [
			'fail',
			'invalid-action',
			'invalid-format',
			'nested:command',
			'test'
		];

		$this->assertSame($expected, $commands);
	}

	public function testCommandWithError(): void
	{
		[$status, $output] = $this->runBinary('fail');
		$this->assertSame(1, $status);
		$this->assertStringContainsString('Something went wrong', $output);
	}

	public function testCommandWithErrorInDebugMode(): void
	{
		// the exception is rethrown and
		// PHP exits with its own error status
		[$status] = $this->runBinary('fail', '--debug');
		$this->assertSame(255, $status);
	}

	public function testCommandWithMissingCommand(): void
	{
		[$status, $output] = $this->runBinary('does-not-exist');
		$this->assertSame(1, $status);
		$this->assertStringContainsString('The command does not exist', $output);
	}

	public function testDir(): void
	{
		$cli = new CLI();

		// current working directory
		$this->assertSame(__DIR__ . '/fixtures', $cli->dir());

		// relative
		$this->assertSame(__DIR__ . '/fixtures/./commands', $cli->dir('./commands'));
		$this->assertSame(__DIR__ . '/fixtures/../commands', $cli->dir('../commands'));

		// absolute
		$this->assertSame('/test', $cli->dir('/test'));

		// an empty string is treated like a missing folder
		$this->assertSame(__DIR__ . '/fixtures', $cli->dir(''));

		// a folder named "0" is a folder, not a missing one
		$this->assertSame('0', $cli->dir('0'));
	}

	public function testHome(): void
	{
		$homeBefore    = getenv('HOME');
		$xdgHomeBefore = getenv('XDG_CONFIG_HOME');

		// unset xdg config home to make sure home is used
		putenv('XDG_CONFIG_HOME');
		putenv('HOME=/test');

		$cli = new CLI();

		$this->assertSame('/test/.kirby', $cli->home());

		putenv('HOME=' . $homeBefore);
		putenv('XDG_CONFIG_HOME=' . $xdgHomeBefore);
	}

	public function testHomeWithXdgConfig(): void
	{
		$before = getenv('XDG_CONFIG_HOME');

		putenv('XDG_CONFIG_HOME=/test');

		$cli = new CLI();

		$this->assertSame('/test/kirby', $cli->home());

		putenv('XDG_CONFIG_HOME=' . $before);
	}

	public function testJson(): void
	{
		$cli = new CLI();
		$json = $cli->json([
			'test' => 'value'
		]);

		$expected  = '{' . PHP_EOL;
		$expected .= '    "test": "value"' . PHP_EOL;
		$expected .= '}';

		$this->assertSame($expected, $json);
	}

	public function testKirby(): void
	{
		$cli = new CLI();

		$this->expectException('Exception');
		$this->expectExceptionMessage('The Kirby installation could not be found');

		$cli->kirby();
	}

	public function testKirbyWithoutFailing(): void
	{
		$cli = new CLI();

		$this->assertNull($cli->kirby(false));
	}

	public function testLoadFromCoreCommands(): void
	{
		$cli = new CLI();

		$command = $cli->load('install');
		$this->assertSame('Installs the kirby folder', $command['description']);
	}

	public function testLoadFromLocalCommands(): void
	{
		$cli = new CLI();

		$command = $cli->load('test');
		$this->assertSame('Test', $command['description']);
	}

	public function testLoadInvalidCommand(): void
	{
		$cli = new CLI();

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('The command does not exist');

		$cli->load('foo');
	}

	public function testLoadInvalidCommandAction(): void
	{
		$cli = new CLI();

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('The command does not define a command action');

		$cli->load('invalid-action');
	}

	public function testLoadInvalidCommandFormat(): void
	{
		$cli = new CLI();

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Invalid command format. The command must be defined as array');

		$cli->load('invalid-format');
	}

	public function testRoot(): void
	{
		$cli = new CLI();

		$this->assertSame(dirname(__DIR__, 2) . '/commands', $cli->root('commands.core'));
		$this->assertSame($cli->home() . '/commands', $cli->root('commands.global'));
		$this->assertSame(__DIR__ . '/fixtures/commands', $cli->root('commands.local'));
	}

	public function testRoots(): void
	{
		$cli = new CLI();
		$roots = $cli->roots();

		$this->assertArrayHasKey('commands.core', $roots);
		$this->assertArrayHasKey('commands.local', $roots);
		$this->assertArrayHasKey('commands.global', $roots);
	}

	public function testTemplate(): void
	{
		$cli = new CLI();

		$result = $cli->template('Hello {{ message }}', ['message' => 'world']);

		$this->assertSame('Hello world', $result);
	}

	public function testVersion(): void
	{
		$cli = new CLI();
		$this->assertMatchesRegularExpression('!^[0-9]+.[0-9]+.[0-9]+$!', $cli->version());
	}
}

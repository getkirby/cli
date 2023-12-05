<?php

namespace Kirby\CLI;

use Exception;
use League\CLImate\CLImate;

/**
 * @coversDefaultClass \Kirby\CLI\CLI
 */
class CLITest extends TestCase
{
	public function setUp(): void
	{
		chdir(__DIR__);
	}

	/**
	 * @covers ::climate
	 */
	public function testClimate()
	{
		$cli = new CLI();
		$this->assertInstanceOf(CLImate::class, $cli->climate());
	}

	/**
	 * @covers ::commandsInDirectory
	 */
	public function testCommandsInDirectory()
	{
		$cli = new CLI();

		// missing command directory
		$commands = $cli->commandsInDirectory(__DIR__ . '/does-not-exist');
		$this->assertSame([], $commands);

		// existing command directory
		$commands = $cli->commandsInDirectory(__DIR__ . '/commands');
		$expected = [
			'invalid-action',
			'invalid-format',
			'nested:command',
			'test'
		];

		$this->assertSame($expected, $commands);
	}

	/**
	 * @covers ::dir
	 */
	public function testDir()
	{
		$cli = new CLI();

		// current working directory
		$this->assertSame(__DIR__, $cli->dir());

		// relative
		$this->assertSame(__DIR__ . '/./commands', $cli->dir('./commands'));
		$this->assertSame(__DIR__ . '/../commands', $cli->dir('../commands'));

		// absolute
		$this->assertSame('/test', $cli->dir('/test'));
	}

	/**
	 * @covers ::home
	 */
	public function testHome()
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

	/**
	 * @covers ::home
	 */
	public function testHomeWithXdgConfig()
	{
		$before = getenv('XDG_CONFIG_HOME');

		putenv('XDG_CONFIG_HOME=/test');

		$cli = new CLI();

		$this->assertSame('/test/kirby', $cli->home());

		putenv('XDG_CONFIG_HOME=' . $before);
	}

	/**
	 * @covers ::json
	 */
	public function testJson()
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

	/**
	 * @covers ::kirby
	 */
	public function testKirby()
	{
		$cli = new CLI();

		$this->expectException('Exception');
		$this->expectExceptionMessage('The Kirby installation could not be found');

		$cli->kirby();
	}

	/**
	 * @covers ::kirby
	 */
	public function testKirbyWithoutFailing()
	{
		$cli = new CLI();

		$this->assertNull($cli->kirby(false));
	}

	/**
	 * @covers ::load
	 */
	public function testLoadFromCoreCommands()
	{
		$cli = new CLI();

		$command = $cli->load('install');
		$this->assertSame('Installs the kirby folder', $command['description']);
	}

	/**
	 * @covers ::load
	 */
	public function testLoadFromLocalCommands()
	{
		$cli = new CLI();

		$command = $cli->load('test');
		$this->assertSame('Test', $command['description']);
	}

	/**
	 * @covers ::load
	 */
	public function testLoadInvalidCommand()
	{
		$cli = new CLI();

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('The command does not exist');

		$cli->load('foo');
	}

	/**
	 * @covers ::load
	 */
	public function testLoadInvalidCommandAction()
	{
		$cli = new CLI();

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('The command does not define a command action');

		$cli->load('invalid-action');
	}

	/**
	 * @covers ::load
	 */
	public function testLoadInvalidCommandFormat()
	{
		$cli = new CLI();

		$this->expectException(Exception::class);
		$this->expectExceptionMessage('Invalid command format. The command must be defined as array');

		$cli->load('invalid-format');
	}

	/**
	 * @covers ::root
	 */
	public function testRoot()
	{
		$cli = new CLI();

		$this->assertSame(dirname(__DIR__, 2) . '/commands', $cli->root('commands.core'));
		$this->assertSame($cli->home() . '/commands', $cli->root('commands.global'));
		$this->assertSame(__DIR__ . '/commands', $cli->root('commands.local'));
	}

	/**
	 * @covers ::roots
	 */
	public function testRoots()
	{
		$cli = new CLI();
		$roots = $cli->roots();

		$this->assertArrayHasKey('commands.core', $roots);
		$this->assertArrayHasKey('commands.local', $roots);
		$this->assertArrayHasKey('commands.global', $roots);
	}

	/**
	 * @covers ::template
	 */
	public function testTemplate()
	{
		$cli = new CLI();

		$result = $cli->template('Hello {{ message }}', ['message' => 'world']);

		$this->assertSame('Hello world', $result);
	}

	/**
	 * @covers ::version
	 */
	public function testVersion()
	{
		$cli = new CLI();
		$this->assertMatchesRegularExpression('!^[0-9]+.[0-9]+.[0-9]+$!', $cli->version());
	}
}

<?php

declare(strict_types = 1);

namespace Kirby\CLI;

use Exception;
use Kirby\Cms\App;
use League\CLImate\CLImate;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;

/**
 * Command Line Interface for Kirby
 *
 * @package   Kirby CLI
 * @author    Bastian Allgeier <bastian@getkirby.com>
 * @link      https://getkirby.com
 * @copyright Bastian Allgeier
 * @license   https://opensource.org/licenses/MIT
 */
class CLI
{
	protected CLImate $climate;
	protected App|null $kirby = null;
	protected array $options;
	protected array $roots;

	/**
	 * Proxy for CLImate methods
	 */
	public function __call(string $method, array $arguments = [])
	{
		return $this->climate->$method(...$arguments);
	}

	/**
	 * Creates a new CLI instance
	 */
	public function __construct()
	{
		$this->climate = new CLImate();
		$this->roots   = [];

		if (function_exists('kirby') === true && class_exists('Kirby\Cms\App') === true) {
			$this->kirby = App::instance();
			$this->roots = $this->kirby->roots()->toArray();
		}

		$this->createCommandRoots();
	}

	/**
	 * Returns the value for an argument if it can be found
	 */
	public function arg(string $name)
	{
		return $this->climate->arguments->get($name);
	}

	/**
	 * Tries to get a value from one
	 * of the arguments and otherwise
	 * shows a prompt for it
	 */
	public function argOrPrompt(string $name, string $prompt, bool $required = true)
	{
		$value = $this->arg($name);

		if (empty($value) === true) {
			return $this->prompt($prompt, $required);
		}

		return $value;
	}

	/**
	 * Returns all parsed arguments
	 */
	public function args()
	{
		return $this->climate->arguments;
	}

	/**
	 * Returns the CLImate instance
	 * if it has been initiated
	 */
	public function climate(): CLImate
	{
		return $this->climate;
	}

	/**
	 * Runs a command with the given arguments
	 */
	public static function command(...$args): void
	{
		$cli = new static();

		try {
			$cli->run(...$args);
		} catch (Throwable $e) {
			$cli->handleException($e);
		}
	}

	/**
	 * Find the absolute path to the command file
	 */
	public function commandFile(string $name, bool $core = true): string
	{
		// load built-in command
		$file = $this->roots['commands.core'] . '/' . $name . '.php';

		if ($core === true && is_file($file) === true) {
			return $file;
		}

		// global commands
		$file = $this->roots['commands.global'] . '/' . $name . '.php';

		if (is_file($file) === true) {
			return $file;
		}

		// local commands
		$file = $this->roots['commands.local'] . '/' . $name . '.php';

		if (is_file($file) === true) {
			return $file;
		}

		throw new Exception('The command does not exist');
	}

	/**
	 * Returns an array with all
	 * global and custom commands
	 */
	public function commands(): array
	{
		$core    = $this->commandsInDirectory($this->roots['commands.core']);
		$global  = $this->commandsInDirectory($this->roots['commands.global']);
		$local   = $this->commandsInDirectory($this->roots['commands.local']);
		$plugins = [];

		foreach ($local as $index => $command) {
			if (in_array($command, $core) === true) {
				unset($local[$index]);
			}
		}

		if ($this->kirby) {
			$extensions = $this->kirby->extensions('commands');

			foreach ($extensions as $name => $command) {
				if (in_array($name, $core) === false) {
					$plugins[] = $name;
				}
			}
		}

		return [
			'core'    => $core,
			'global'  => $global,
			'custom'  => $local,
			'plugins' => $plugins
		];
	}

	/**
	 * Scans a directory and finds all
	 * valid commands inside.
	 */
	public function commandsInDirectory(string $directory): array
	{
		$directory = realpath($directory);

		if (!$directory || is_dir($directory) === false) {
			return [];
		}

		$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));
		$commands = [];

		foreach ($iterator as $item) {
			if ($item->isFile() === false || $item->getExtension() !== 'php') {
				continue;
			}

			$path = preg_replace('!^' . preg_quote($directory) . '\/!', '', $item->getPathname());
			$path = preg_replace('!.php$!', '', $path);

			if (str_contains($path, '_templates') === true) {
				continue;
			}

			$commands[] = str_replace('/', ':', $path);
		}

		asort($commands);

		return array_values($commands);
	}

	/**
	 * Shows a prompt which has to be confirmed
	 * in order to execute the callback
	 */
	public function confirmToContinue(string $message, ?callable $onExit = null): void
	{
		$input = $this->confirm($message);

		if ($input->confirmed() === false) {
			if (is_callable($onExit) === true) {
				$onExit();
			}
			exit;
		}
	}

	/**
	 * Shows a prompt before a file or directory
	 * ($item) will be deleted
	 */
	public function confirmToDelete(string $item, string $message): bool
	{
		$item = realpath($item);

		if (!$item) {
			return true;
		}

		if (is_dir($item) === false && is_file($item) === false && is_link($item) === false) {
			return true;
		}

		$this->confirmToContinue($message);

		// we need to implement Dir::remove and F::remove here again, because
		// the Kirby installation might not be available when we need this
		if (is_dir($item) === true) {
			$this->rmdir($item);
		} else {
			unlink($item);
		}

		return true;
	}

	/**
	 * Creates default values for command roots
	 * if they are not set
	 */
	protected function createCommandRoots(): void
	{
		$local = $this->kirby?->root('commands') ?? getcwd() . '/commands';

		$this->roots['commands.core']   ??= dirname(__DIR__, 2) . '/commands';
		$this->roots['commands.global'] ??= $this->home() . '/commands';
		$this->roots['commands.local']  ??= $local;
	}

	/**
	 * Creates all custom roots for the CLI
	 */
	protected function createRoots(array $roots = []): array
	{
		foreach ($roots as $key => $value) {
			if (str_starts_with($value, '.') === true) {
				$root = realpath(getcwd() . '/' . $value);

				if ($root !== false) {
					$roots[$key] = $root;
				}
			} else {
				$roots[$key] = $value;
			}
		}

		return $roots;
	}

	/**
	 * Get the current working directory
	 */
	public function dir(?string $folder = null): string
	{
		$current = getcwd();

		if (empty($folder) === true) {
			return $current;
		}

		if (str_starts_with($folder, '.') === true) {
			return $current . '/' . $folder;
		}

		return $folder;
	}

	/**
	 * Handles exception with throwing exception or out error message
	 */
	protected function handleException(Throwable $e): never
	{
		if ($this->isDefined('debug') === true) {
			throw $e;
		}

		$this->error($e->getMessage());
		exit;
	}

	/**
	 * Gets path for global commands (respecting 'XDG_CONFIG_HOME' if set)
	 *
	 * For more information on the 'XDG Base Directory Speicfications',
	 * see https://specifications.freedesktop.org/basedir-spec/latest
	 */
	public function home(): string
	{
		if ($path = getenv('XDG_CONFIG_HOME')) {
			return $path . '/kirby';
		}

		return getenv('HOME') . '/.kirby';
	}

	/**
	 * Checks if an argument is set
	 */
	public function isDefined(string $arg): bool
	{
		return $this->climate->arguments->defined($arg);
	}

	/**
	 * Creates pretty json
	 */
	public function json(array $data = []): string
	{
		return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
	}

	/**
	 * Returns the parent Kirby instance
	 * if an installation can be found
	 */
	public function kirby(bool $fail = true): ?App
	{
		if (is_a($this->kirby, 'Kirby\Cms\App') === false) {
			if ($fail === true) {
				throw new Exception('The Kirby installation could not be found');
			}

			return null;
		}

		return $this->kirby;
	}

	/**
	 * Loads a command either from the custom
	 * site/commands directory of the current Kirby
	 * installation or from the global commands
	 * directory of the CLI. Global commands will always
	 * overwrite local commands because they sometimes
	 * depend on each other.
	 */
	public function load(string $name): callable|array
	{
		// convert the name to a path
		$path = str_replace(':', '/', $name);

		try {
			$command = require $this->commandFile($path);
		} catch (Throwable $e) {
			if (!$this->kirby) {
				throw $e;
			}

			// try to load a plugin command
			$command = $this->kirby->extension('commands', $name);

			if (empty($command) === true) {
				throw $e;
			}
		}

		// validate the command format
		if (is_array($command) === false) {
			throw new Exception('Invalid command format. The command must be defined as array');
		}

		// validate that the command can be executed
		if (isset($command['command']) === false || is_callable($command['command']) === false) {
			throw new Exception('The command does not define a command action');
		}

		return $command;
	}

	/**
	 * Creates a file with the given content
	 * and replaces all placeholders in the content
	 * with values from the data array. $content
	 * can also be a filename and the method will
	 * automatically fetch content from the file
	 */
	public function make(string $file, string $content, array $data = []): bool
	{
		if (is_file($content) === true) {
			$content = file_get_contents($content);
		}

		$file    = $this->template($file, $data);
		$content = $this->template($content, $data);
		$dir     = dirname($file);

		$this->confirmToDelete($file, 'The file ' . basename($file) . ' exists. Do you want to replace it?');

		if (is_dir($dir) === false) {
			mkdir($dir, 0755, true);
		}

		return file_put_contents($file, $content) !== false;
	}

	/**
	 * Shows a prompt and returns the
	 * entered value.
	 */
	public function prompt(string $prompt, bool $required = true)
	{
		while (empty($value) === true) {
			$input = $this->input($prompt);
			$value = $input->prompt();

			if ($required === false) {
				return $value;
			}
		}

		return $value;
	}

	/**
	 * Removes a folder including all containing files and folders
	 */
	public function rmdir($dir): bool
	{
		$dir = realpath($dir);

		if (is_dir($dir) === false) {
			return true;
		}

		if (is_link($dir) === true) {
			return unlink($dir);
		}

		foreach (scandir($dir) as $childName) {
			if (in_array($childName, ['.', '..']) === true) {
				continue;
			}

			$child = $dir . '/' . $childName;

			if (is_dir($child) === true && is_link($child) === false) {
				$this->rmdir($child);
			} else {
				unlink($child);
			}
		}

		return rmdir($dir);
	}

	/**
	 * Returns a root either from the custom roots
	 * array or from the Kirby instance
	 */
	public function root(string $key): ?string
	{
		return $this->roots[$key] ?? $this->kirby?->root($key);
	}

	/**
	 * Returns all roots
	 */
	public function roots(): array
	{
		return $this->roots;
	}

	/**
	 * Load and execute a command
	 */
	public function run(?string $name = null, ...$args): void
	{
		// create clean new climate instance
		$this->climate = new CLImate();

		// custom commands
		$this->climate->style->addCommand('success', ['background_light_green', 'black']);

		// no command? show info about the cli
		if (empty($name) === true) {
			$this->run('help');
			return;
		}

		// add the command as first argument
		$this->climate->arguments->add([
			'command' => [
				'description' => 'The name of the command',
				'required' => true
			]
		]);

		$command = $this->load($name);

		$this->climate->arguments->add($command['args'] ?? []);
		$this->climate->description($command['description'] ?? 'kirby ' . $name);

		// add the quiet option
		$this->climate->arguments->add([
			'quiet' => [
				'description' => 'Surpresses any output',
				'longPrefix'  => 'quiet',
				'noValue'     => true
			]
		]);

		// add debug argument
		$this->climate->arguments->add([
			'debug' => [
				'description' => 'Enables debug mode',
				'prefix'      => 'd',
				'longPrefix'  => 'debug',
				'noValue'     => true
			]
		]);

		// add help argument
		$this->climate->arguments->add([
			'help' => [
				'description' => 'Prints a usage statement',
				'prefix'      => 'h',
				'longPrefix'  => 'help',
				'noValue'     => true
			]
		]);

		// build the args array
		$argv = [
			'kirby',
			$name,
			...$args
		];

		try {
			$this->climate->arguments->parse($argv);
		} catch (Throwable $e) {
			$this->handleException($e);
		}

		// enable quiet mode
		if ($this->climate->arguments->get('quiet')) {
			$this->climate->output->add('quiet', new QuietWriter());
			$this->climate->output->defaultTo('quiet');
		}

		if ($this->climate->arguments->defined('help', $argv)) {
			$this->climate->usage($argv);
			return;
		}

		try {
			$command['command']($this);
		} catch (Throwable $e) {
			$this->handleException($e);
		}
	}

	/**
	 * Replaces placeholders in string templates
	 * Str::replace would be better but is only
	 * available if Kirby is installed
	 */
	public function template(string $template, array $data = []): string
	{
		$keys = array_map(function ($key) {
			return '{{ ' . $key . ' }}';
		}, array_keys($data));

		return str_replace($keys, array_values($data), $template);
	}

	/**
	 * Returns the CLI version from the composer.json
	 */
	public function version(): string
	{
		$composer = dirname(__DIR__, 2) . '/composer.json';
		$contents = file_get_contents($composer);
		$json     = json_decode($contents);

		return $json->version;
	}
}

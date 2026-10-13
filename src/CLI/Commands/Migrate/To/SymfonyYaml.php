<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Migrate\To;

use Exception;
use FilesystemIterator;
use Kirby\CLI\CLI;
use Kirby\CLI\Command;
use Kirby\Cms\App;
use Kirby\Cms\Language;
use Kirby\Cms\ModelWithContent;
use Kirby\Content\VersionId;
use Kirby\Data\Yaml;
use Kirby\Data\YamlSpyc;
use Kirby\Data\YamlSymfony;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Throwable;

class SymfonyYaml extends Command
{
	/**
	 * Field types that store their value as YAML,
	 * incl. the Kirby 5 names of the picker fields
	 */
	protected const FIELD_TYPES = [
		'entries',
		'filepicker',
		'files',
		'object',
		'pagepicker',
		'pages',
		'structure',
		'userpicker',
		'users',
	];

	public static function args(): array
	{
		return [
			'dry-run' => [
				'description' => 'List the issues without converting any content',
				'longPrefix'  => 'dry-run',
				'noValue'     => true,
			],
		];
	}

	/**
	 * Returns all blueprint files that Kirby reads as YAML,
	 * resolved like `Blueprint::find()`: the `.yml` files
	 * in the blueprints folder and the blueprint files
	 * that plugins register
	 */
	protected static function blueprintFiles(App $kirby): array
	{
		$root  = $kirby->root('blueprints');
		$files = [];

		if (is_dir($root) === true) {
			$iterator = new RecursiveIteratorIterator(
				new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
			);

			foreach ($iterator as $file) {
				if ($file->getExtension() === 'yml') {
					$files[] = $file->getPathname();
				}
			}
		}

		foreach ($kirby->extensions('blueprints') as $name => $blueprint) {
			// a blueprint in the blueprints folder
			// replaces the registered one
			if (is_file($root . '/' . $name . '.yml') === true) {
				continue;
			}

			// a callback can return a blueprint file path
			if (is_callable($blueprint) === true) {
				$blueprint = $blueprint($kirby);
			}

			// arrays have already been decoded by the plugin
			if (
				is_string($blueprint) === true &&
				in_array(pathinfo($blueprint, PATHINFO_EXTENSION), ['yml', 'yaml'], true) === true
			) {
				$files[] = $blueprint;
			}
		}

		$core = realpath($kirby->root('kirby')) . '/';
		$yaml = [];

		foreach ($files as $file) {
			$file = realpath($file);

			// Kirby's own blueprints are ready for Symfony YAML
			if (
				$file === false ||
				str_starts_with($file, $core) === true
			) {
				continue;
			}

			$yaml[$file] = $file;
		}

		ksort($yaml);

		return array_values($yaml);
	}

	/**
	 * Lists all blueprints that Symfony YAML cannot read
	 * or reads differently than Spyc and returns the number
	 * of blueprints with issues and of those with errors
	 *
	 * @return array{issues: int, errors: int}
	 */
	protected static function checkBlueprints(CLI $cli, App $kirby): array
	{
		$cli->br();
		$cli->bold('Blueprints');
		$cli->br();

		$issues = 0;
		$errors = 0;

		foreach (static::blueprintFiles($kirby) as $file) {
			try {
				$lines = static::diff((string)file_get_contents($file));
			} catch (Throwable $e) {
				$lines = [$e->getMessage()];
				$errors++;
			}

			if ($lines !== []) {
				static::report($cli, static::path($cli, $file), $lines);
				$issues++;
			}
		}

		if ($issues === 0) {
			$cli->out('✅ All blueprints are ready for Symfony YAML');
		} else {
			$cli->br();
			$cli->out('💡 Fix these blueprints by hand, e.g. by putting the values in quotes.');
		}

		return [
			'issues' => $issues,
			'errors' => $errors,
		];
	}

	/**
	 * Lists all content files with YAML fields that need to
	 * be converted and returns them for the conversion
	 */
	protected static function checkContent(CLI $cli, App $kirby): array
	{
		$cli->br();
		$cli->bold('Content');
		$cli->br();

		$files = [];

		foreach ($kirby->models() as $model) {
			$names = static::yamlFields($model);

			if ($names === []) {
				continue;
			}

			$storage = $model->storage();

			foreach ($storage->all() as $versionId => $language) {
				$fields = static::contentFields($storage->read($versionId, $language), $names);

				if ($fields === []) {
					continue;
				}

				static::report(
					$cli,
					static::contentFile($cli, $model, $versionId, $language),
					array_merge(...array_values($fields)),
					limit: 5
				);

				$files[] = [
					'model'    => $model,
					'version'  => $versionId,
					'language' => $language,
					'fields'   => array_keys($fields),
				];
			}
		}

		if ($files === []) {
			$cli->out('✅ All content is ready for Symfony YAML');
		}

		return $files;
	}

	public static function command(CLI $cli): void
	{
		$kirby  = $cli->kirby();
		$dryrun = $cli->arg('dry-run');

		if (version_compare($kirby->version(), '5.0.0', '<') === true) {
			throw new Exception('The migration requires Kirby 5 or newer');
		}

		$kirby->impersonate('kirby');

		$blueprints = static::checkBlueprints($cli, $kirby);
		$content    = static::checkContent($cli, $kirby);

		if ($content !== []) {
			$cli->br();

			if (static::readsSpyc() === true) {
				$cli->out('💡 Kirby still reads YAML with Spyc. Run the command again after the update to Kirby 6 to convert these content files. If you have set the yaml.handler option to spyc, remove it first.');
			} elseif ($blueprints['errors'] > 0) {
				$cli->out('💡 Fix the blueprints first and run the command again. Kirby cannot load blueprints with errors, so this list of content files might be incomplete.');
			} elseif ($dryrun === true) {
				$cli->out('💡 Run the command without --dry-run to convert these content files.');
			} else {
				$cli->confirmToContinue('Convert the YAML in these content files to Symfony YAML? Make sure that you have a backup of your content.');
				$cli->br();

				$content = static::convertContent($cli, $content);
			}
		}

		if ($blueprints['issues'] > 0 || $content !== []) {
			throw new Exception('The migration to Symfony YAML is not complete yet');
		}

		$cli->br();
		$cli->success('Your site is ready for Symfony YAML');
	}

	/**
	 * Returns the issues for each YAML field
	 * in the content that needs to be converted
	 *
	 * @return array<string, array<int, string>>
	 */
	protected static function contentFields(array $content, array $names): array
	{
		$fields = [];

		foreach ($content as $name => $value) {
			$name = (string)$name;

			if (
				is_string($value) === false ||
				in_array(strtolower($name), $names, true) === false
			) {
				continue;
			}

			$lines = static::needsConversion($value, $name);

			if ($lines !== []) {
				$fields[$name] = $lines;
			}
		}

		return $fields;
	}

	/**
	 * Returns the path to the content file or
	 * a description of the model, if the content
	 * is not stored in text files
	 */
	protected static function contentFile(
		CLI $cli,
		ModelWithContent $model,
		VersionId $versionId,
		Language $language
	): string {
		$storage = $model->storage();

		if (is_callable([$storage, 'contentFile']) === true) {
			return static::path($cli, $storage->contentFile($versionId, $language));
		}

		return trim($model::CLASS_ALIAS . ' ' . $model->id()) . ' (' . $versionId->value() . ', ' . $language->code() . ')';
	}

	/**
	 * Converts the YAML fields of one content file
	 * from Spyc to Symfony YAML
	 *
	 * @throws Exception If Symfony YAML would read the converted YAML differently
	 */
	protected static function convert(
		ModelWithContent $model,
		VersionId $versionId,
		Language $language,
		array $fields
	): void {
		$storage = $model->storage();
		$content = $storage->read($versionId, $language);
		$changed = false;

		foreach ($fields as $name) {
			$value = $content[$name] ?? null;

			// skip fields that have been saved
			// by someone else in the meantime
			if (
				is_string($value) === false ||
				static::needsConversion($value, (string)$name) === []
			) {
				continue;
			}

			$data      = YamlSpyc::decode($value);
			$converted = YamlSymfony::encode($data);

			// make sure that Symfony YAML reads the
			// converted YAML like Spyc read the original
			if (YamlSymfony::decode($converted) !== $data) {
				throw new Exception('The "' . $name . '" field cannot be converted without changing its data');
			}

			$content[$name] = $converted;
			$changed        = true;
		}

		// write the content file directly to keep
		// the lock of unsaved changes and all other fields
		if ($changed === true) {
			$storage->update($versionId, $language, $content);
		}
	}

	/**
	 * Converts all given content files and returns
	 * those that could not be converted
	 */
	protected static function convertContent(CLI $cli, array $files): array
	{
		$failed = [];

		foreach ($files as $file) {
			$path = static::contentFile($cli, $file['model'], $file['version'], $file['language']);

			try {
				static::convert($file['model'], $file['version'], $file['language'], $file['fields']);
				$cli->out('✅ ' . $path);
			} catch (Throwable $e) {
				$cli->out('🚨 ' . $path . ': ' . $e->getMessage());
				$failed[] = $file;
			}
		}

		return $failed;
	}

	public static function description(): string
	{
		return 'Checks blueprints and converts content from Spyc to Symfony YAML';
	}

	/**
	 * Returns a line for each value that Spyc
	 * and Symfony YAML read differently
	 *
	 * @throws Throwable If Symfony YAML cannot read the YAML
	 */
	protected static function diff(string $yaml, string|null $path = null): array
	{
		return static::differences(
			YamlSpyc::decode($yaml),
			YamlSymfony::decode($yaml),
			$path
		);
	}

	protected static function differences(
		mixed $spyc,
		mixed $symfony,
		string|null $path
	): array {
		if (
			is_array($spyc) === true &&
			is_array($symfony) === true
		) {
			$lines = [];
			$keys  = array_unique([
				...array_keys($spyc),
				...array_keys($symfony)
			]);

			foreach ($keys as $key) {
				$lines = [
					...$lines,
					...static::differences(
						$spyc[$key] ?? null,
						$symfony[$key] ?? null,
						$path === null ? (string)$key : $path . '.' . $key
					)
				];
			}

			return $lines;
		}

		// ignore trailing line breaks in multi-line strings
		$spyc    = is_string($spyc) === true ? trim($spyc) : $spyc;
		$symfony = is_string($symfony) === true ? trim($symfony) : $symfony;

		if ($spyc == $symfony) {
			return [];
		}

		return [($path ?? 'value') . ': ' . static::export($spyc) . ' → ' . static::export($symfony)];
	}

	/**
	 * Returns a short JSON representation of the value
	 */
	protected static function export(mixed $value): string
	{
		$json = (string)json_encode(
			$value,
			JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR
		);

		if (mb_strlen($json) > 60) {
			return mb_substr($json, 0, 60) . '…';
		}

		return $json;
	}

	/**
	 * Checks if Symfony YAML would write the YAML exactly
	 * like this, i.e. it has already been converted or
	 * saved with Symfony YAML
	 */
	protected static function isSymfonyYaml(string $yaml): bool
	{
		try {
			return trim(YamlSymfony::encode(YamlSymfony::decode($yaml))) === trim($yaml);
		} catch (Throwable) {
			return false;
		}
	}

	/**
	 * Returns the issues of a YAML field in the content
	 * or an empty array if it does not need to be converted
	 */
	protected static function needsConversion(
		string $yaml,
		string $field
	): array {
		try {
			$lines = static::diff($yaml, $field);
		} catch (Throwable $e) {
			return [$field . ': ' . $e->getMessage()];
		}

		// Spyc misreads some of the YAML that Symfony YAML writes,
		// e.g. multi-line strings; reading such YAML with Spyc
		// to convert it again would break the content
		if ($lines !== [] && static::isSymfonyYaml($yaml) === true) {
			return [];
		}

		return $lines;
	}

	/**
	 * Returns the path relative to the working directory
	 */
	protected static function path(CLI $cli, string $file): string
	{
		$dir = $cli->dir() . '/';

		if (str_starts_with($file, $dir) === true) {
			return substr($file, strlen($dir));
		}

		return $file;
	}

	/**
	 * Checks if Kirby reads YAML with Spyc, which is the
	 * default in Kirby 5 and can be enabled in Kirby 6
	 * with the `yaml.handler` option
	 */
	protected static function readsSpyc(): bool
	{
		return in_array(Yaml::handler(), [null, 'symfony'], true) === false;
	}

	protected static function report(
		CLI $cli,
		string $title,
		array $lines,
		int|null $limit = null
	): void {
		$cli->out($title);

		foreach (array_slice($lines, 0, $limit) as $line) {
			$cli->out('  ' . $line);
		}

		if ($limit !== null && count($lines) > $limit) {
			$cli->out('  … and ' . (count($lines) - $limit) . ' more');
		}
	}

	/**
	 * Returns the lowercase names of all
	 * fields in the blueprint that store YAML
	 */
	protected static function yamlFields(ModelWithContent $model): array
	{
		$names = [];

		foreach ($model->blueprint()->fields() as $name => $field) {
			if (in_array($field['type'] ?? null, static::FIELD_TYPES, true) === true) {
				$names[] = strtolower((string)$name);
			}
		}

		return $names;
	}
}

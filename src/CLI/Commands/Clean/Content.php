<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Clean;

use Generator;
use Kirby\CLI\CLI;
use Kirby\CLI\Command;
use Kirby\Cms\Languages;

class Content extends Command
{
	public static function args(): array
	{
		return [
			'dry-run' => [
				'description' => 'Run the command without actually updating content',
				'noValue'     => true,
			],
		];
	}

	protected static function cleanContent(
		CLI $cli,
		Generator $collection,
		string $lang,
		array $ignore = [],
		bool $dryrun = false,
	): void {
		foreach ($collection as $item) {
			static::cleanItem($cli, $item, $lang, $ignore, $dryrun);
		}
	}

	protected static function cleanItem(
		CLI $cli,
		$item,
		string $lang,
		array $ignore,
		bool $dryrun
	): void {
		$fieldsToDelete = static::fieldsToDelete($item, $lang, $ignore);

		if (count($fieldsToDelete) === 0) {
			return;
		}

		// build data array with field names as keys and null as values
		$data = [];

		foreach ($fieldsToDelete as $field) {
			$data[$field] = null;
			$cli->out('Remove "' . $field . '" from ' . $item::CLASS_ALIAS . ' (' . $item->id() . ')');
		}

		// don't update models that have changes
		if ($item->version('changes')->exists($lang) === true) {
			$cli->error('The ' . $item::CLASS_ALIAS . ' (' . $item->id() . ') has changes and cannot be cleaned. Save the changes and try again.');
		}

		if ($dryrun === true) {
			return;
		}

		static::updateVersion($item, $lang, $data);
	}

	public static function command(CLI $cli): void
	{
		$kirby  = $cli->kirby();
		$dryrun = $cli->arg('dry-run');

		if ($dryrun === false) {
			$cli->confirmToContinue('This will delete all fields from content files that are not defined in blueprints, no matter if they contain content or not. Are you sure?');
		}

		// Authenticate as almighty
		$kirby->impersonate('kirby');

		// set the fields to be ignored
		$ignore = ['uuid', 'title', 'slug', 'template', 'sort', 'focus'];

		// go through all languages
		foreach (Languages::ensure() as $language) {
			// should call kirby models for each loop
			// since generators cannot be cloned
			// otherwise run into an exception
			static::cleanContent(
				cli: $cli,
				collection: $kirby->models(),
				lang: $language->code(),
				ignore: $ignore,
				dryrun: $dryrun
			);
		}

		$cli->success('The content files have been cleaned');
	}

	public static function description(): string|null
	{
		return 'Deletes all fields from page, file or user content files that are not defined in the blueprint, no matter if they contain content or not.';
	}

	/**
	 * Returns an array of field names that are in the content
	 * but not defined in the blueprint
	 */
	protected static function fieldsToDelete($item, string $lang, array $ignore): array
	{
		$contentFields = $item->content($lang)->fields();

		// remove ignored fields
		foreach ($ignore as $field) {
			unset($contentFields[$field]);
		}

		// get the original field names
		$contentFieldNames = array_keys($contentFields);

		// get all field keys from blueprint (lowercase)
		$blueprintFields = array_keys($item->blueprint()->fields());
		$blueprintLower  = array_map('mb_strtolower', $blueprintFields);

		// find fields not in blueprint
		$fieldsToDelete = [];

		foreach ($contentFieldNames as $field) {
			if (in_array(mb_strtolower($field), $blueprintLower, true) === false) {
				$fieldsToDelete[] = $field;
			}
		}

		return $fieldsToDelete;
	}

	protected static function updateVersion($item, string $lang, array $data): void
	{
		$version = $item->version('latest');

		if ($version->exists($lang) === true) {
			$version->update($data, $lang);
		}
	}
}

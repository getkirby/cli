<?php

declare(strict_types = 1);

use Kirby\CLI\CLI;
use Kirby\Cms\Languages;

$cleanContent = function (
	CLI $cli,
	Generator $collection,
	string $lang,
	array $ignore = [],
	bool $dryrun = false,
): void {
	foreach ($collection as $item) {
		// get all fields in the content file
		$contentFields = $item->content($lang)->fields();

		// unset all fields in the `$ignore` array
		foreach ($ignore as $field) {
			if (array_key_exists($field, $contentFields) === true) {
				unset($contentFields[$field]);
			}
		}

		// get the keys and normalize to lowercase
		$originalContentKeys = array_keys($contentFields);
		$contentFieldKeys    = array_map('mb_strtolower', $originalContentKeys);

		// get all field keys from blueprint and normalize to lowercase
		$blueprintFields    = array_keys($item->blueprint()->fields());
		$blueprintFieldKeys = array_map('mb_strtolower', $blueprintFields);

		// get all field keys that are in $contentFieldKeys but not in $blueprintFieldKeys
		$fieldsToBeDeleted = array_diff($contentFieldKeys, $blueprintFieldKeys);

		// update page only if there are any fields to be deleted
		if (count($fieldsToBeDeleted) === 0) {
			continue;
		}

		// create a mapping: lowercase => original field name
		$lowercaseToOriginal = array_combine($contentFieldKeys, $originalContentKeys);

		// build data array with original field names as keys and null as values
		$data = [];

		foreach ($fieldsToBeDeleted as $lowercaseField) {
			$originalField = $lowercaseToOriginal[$lowercaseField];
			$data[$originalField] = null;

			$cli->out('Remove "' . $originalField . '" from ' . $item::CLASS_ALIAS . ' (' . $item->id() . ')');
		}

		// don't update models that have changes
		if ($item->version('changes')->exists($lang) === true) {
			$cli->error('The ' . $item::CLASS_ALIAS . ' (' . $item->id() . ') has changes and cannot be cleaned. Save the changes and try again.');
		}

		// in a dry-run, the models will not be updated
		if ($dryrun === true) {
			continue;
		}

		// get the latest version of the item
		$version = $item->version('latest');

		// check if the version exists for the given language
		// and try to update the page with the data
		if ($version->exists($lang) === true) {
			$version->update($data, $lang);
		}
	}
};

return [
	'description' => 'Deletes all fields from page, file or user content files that are not defined in the blueprint, no matter if they contain content or not.',
	'args' => [
		'dry-run' => [
			'description' => 'Run the command without actually updating content',
			'noValue'     => true,
		],
	],
	'command' => static function (CLI $cli) use ($cleanContent): void {

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
			$cleanContent(
				cli: $cli,
				collection: $kirby->models(),
				lang: $language->code(),
				ignore: $ignore,
				dryrun: $dryrun
			);
		}

		$cli->success('The content files have been cleaned');
	}
];

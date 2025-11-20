<?php

declare(strict_types = 1);

use Kirby\CLI\CLI;

$cleanContent = function (
	Generator $collection,
	array|null $ignore = null,
	string|null $lang = null
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
		$contentFieldKeys = array_map('mb_strtolower', $originalContentKeys);

		// get all field keys from blueprint and normalize to lowercase
		$blueprintFields = array_keys($item->blueprint()->fields());
		$blueprintFieldKeys = array_map('mb_strtolower', $blueprintFields);

		// get all field keys that are in $contentFieldKeys but not in $blueprintFieldKeys
		$fieldsToBeDeleted = array_diff($contentFieldKeys, $blueprintFieldKeys);

		// update page only if there are any fields to be deleted
		if (count($fieldsToBeDeleted) > 0) {
			// create a mapping: lowercase => original field name
			$lowercaseToOriginal = array_combine($contentFieldKeys, $originalContentKeys);

			// build data array with original field names as keys and null as values
			$data = [];
			foreach ($fieldsToBeDeleted as $lowercaseField) {
				$originalField = $lowercaseToOriginal[$lowercaseField];
				$data[$originalField] = null;
			}

			// get the latest version of the item
			$version = $item->version('latest');

			// check if the version exists for the given language
			// and try to update the page with the data
			if ($version->exists($lang ?? 'default') === true) {
				$version->update($data, $lang ?? 'default');
			}
		}
	}
};

return [
	'description' => 'Deletes all fields from page, file or user content files that are not defined in the blueprint, no matter if they contain content or not.',
	'command' => static function (CLI $cli) use ($cleanContent): void {

		$cli->confirmToContinue('This will delete all fields from content files that are not defined in blueprints, no matter if they contain content or not. Are you sure?');

		$kirby = $cli->kirby();

		// Authenticate as almighty
		$kirby->impersonate('kirby');

		// set the fields to be ignored
		$ignore = ['uuid', 'title', 'slug', 'template', 'sort', 'focus'];

		// call the script for all languages if multilang
		if ($kirby->multilang() === true) {
			$languages = $kirby->languages();

			foreach ($languages as $language) {
				// should call kirby models for each loop
				// since generators cannot be cloned
				// otherwise run into an exception
				$collection = $kirby->models();

				$cleanContent($collection, $ignore, $language->code());
			}

		} else {
			$collection = $kirby->models();
			$cleanContent($collection, $ignore);
		}

		$cli->success('The content files have been cleaned');
	}
];

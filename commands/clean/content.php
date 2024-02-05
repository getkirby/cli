<?php

declare(strict_types = 1);

use Kirby\CLI\CLI;

function clean(
	Generator $collection,
	array|null $ignore = null,
	string|null $lang = null
): void {
	foreach($collection as $item) {
		// get all fields in the content file
		$contentFields = $item->content($lang)->fields();

		// unset all fields in the `$ignore` array
		foreach ($ignore as $field) {
			if (array_key_exists($field, $contentFields) === true) {
				unset($contentFields[$field]);
			}
		}

		// get the keys
		$contentFields = array_keys($contentFields);

		// get all field keys from blueprint
		$blueprintFields = array_keys($item->blueprint()->fields());

		// get all field keys that are in $contentFields but not in $blueprintFields
		$fieldsToBeDeleted = array_diff($contentFields, $blueprintFields);

		// update page only if there are any fields to be deleted
		if (count($fieldsToBeDeleted) > 0) {

			// flip keys and values and set new values to null
			$data = array_map(fn ($value) => null, array_flip($fieldsToBeDeleted));

			// try to update the page with the data
			try {
				$item->update($data, $lang);
			} catch (Exception $e) {
				throw $e->getMessage();
			}
		}
	}
}

return [
	'description' => 'Deletes all fields from page, file or user content files that are not defined in the blueprint, no matter if they contain content or not.',
	'command' => static function (CLI $cli): void {
		$kirby = $cli->kirby();

		// Authenticate as almighty
		$kirby->impersonate('kirby');

		// Define your collection
		$collection = $kirby->models();

		// set the fields to be ignored
		$ignore = ['uuid', 'title', 'slug', 'template', 'sort', 'focus'];

		// call the script for all languages if multilang
		if ($kirby->multilang() === true) {
			$languages = $kirby->languages();

			foreach ($languages as $language) {
				clean($collection, $ignore, $language->code());
			}

		} else {
			clean($collection, $ignore);
		}

		$cli->success('The content files have been cleaned');
	}
];

<?php

declare(strict_types = 1);

use Kirby\CLI\CLI;
use Kirby\Cms\Language;

return [
	'description' => 'Creates a new language',
	'args' => [
		'code' => [
			'description'  => 'The code of the language'
		],
		'name' => [
			'description'  => 'The name of the language'
		],
		'locale' => [
			'description'  => 'The locale of the language'
		],
		'direction' => [
			'description'  => 'The direction of the language'
		]
	],
	'command' => static function (CLI $cli): void {
		$kirby     = $cli->kirby();
		$code      = $cli->argOrPrompt('code', 'Enter a language code:');
		$name      = $cli->argOrPrompt('name', 'Enter a language name (optional):', false);
		$locale    = $cli->argOrPrompt('locale', 'Enter a language locale (optional):', false);
		$direction = $cli->radio('Select language direction:', ['ltr', 'rtl'])->prompt();

		// authenticate as almighty
		$kirby->impersonate('kirby');

		Language::create([
			'code'      => $code,
			'name'      => empty($name) === false ? $name : $code,
			'locale'    => $locale,
			'direction' => $direction,
			'default'   => $kirby->languages()->count() === 0,
		]);

		$cli->success('The language has been created');
	}
];

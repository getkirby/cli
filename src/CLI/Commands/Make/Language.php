<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Make;

use Kirby\CLI\CLI;
use Kirby\CLI\Command;
use Kirby\Cms\Language as LanguageModel;

class Language extends Command
{
	public static function args(): array
	{
		return [
			'code' => [
				'description' => 'The code of the language'
			],
			'name' => [
				'description' => 'The name of the language'
			],
			'locale' => [
				'description' => 'The locale of the language'
			],
			'direction' => [
				'description' => 'The direction of the language'
			]
		];
	}

	public static function command(CLI $cli): void
	{
		$kirby     = $cli->kirby();
		$code      = $cli->argOrPrompt('code', 'Enter a language code:');
		$name      = $cli->argOrPrompt('name', 'Enter a language name (optional):', false);
		$locale    = $cli->argOrPrompt('locale', 'Enter a language locale (optional):', false);
		$direction = $cli->radio('Select language direction:', ['ltr', 'rtl'])->prompt();

		// authenticate as almighty
		$kirby->impersonate('kirby');

		LanguageModel::create([
			'code'      => $code,
			'name'      => empty($name) === false ? $name : $code,
			'locale'    => $locale,
			'direction' => $direction,
			'default'   => $kirby->languages()->count() === 0,
		]);

		$cli->success('The language has been created');
	}

	public static function description(): string|null
	{
		return 'Creates a new language';
	}
}

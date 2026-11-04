<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands;

use Kirby\CLI\CLI;
use Kirby\CLI\Command;
use Kirby\Http\Remote;
use Kirby\Http\Url;
use Kirby\Toolkit\I18n;

class Security extends Command
{
	public static function command(CLI $cli): void
	{
		$kirby        = $cli->kirby();
		$system       = $kirby->system();
		$updateStatus = $system->updateStatus();

		/** @var string[] $messages */
		$messages = $updateStatus?->exceptionMessages() ?? [];
		$messages = [
			...array_column($updateStatus?->messages() ?? [], 'text'),
			...$messages
		];

		if ($kirby->option('debug', false) === true) {
			$messages[] = I18n::translate('system.issues.debug');
		}

		if ($kirby->environment()->https() !== true) {
			$messages[] = I18n::translate('system.issues.https');
		}

		// checks exposable urls of the site
		// works only site url is absolute since can't get it in CLI mode
		// and CURL won't work for relative urls
		if (Url::isAbsolute($kirby->url())) {
			$urls = [
				'content' => $system->exposedFileUrl('content'),
				'git'     => $system->exposedFileUrl('git'),
				'kirby'   => $system->exposedFileUrl('kirby'),
				'site'    => $system->exposedFileUrl('site')
			];

			foreach ($urls as $key => $url) {
				if (empty($url) === false && Remote::get($url)->code() < 400) {
					$messages[] = I18n::translate('system.issues.' . $key);
				}
			}
		} else {
			$messages[] = 'Could not check for exposed folders as the site URL is not absolute';
		}

		if (empty($messages) === false) {
			foreach ($messages as $message) {
				$cli->error('> ' . $message);
			}
		} else {
			$cli->success('Basic security checks were successful, please review https://getkirby.com/docs/guide/security for additional best practices.');
		}
	}

	public static function description(): string|null
	{
		return 'Performs security checks of the site';
	}
}

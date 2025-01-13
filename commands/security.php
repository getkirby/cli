<?php

declare(strict_types = 1);

use Kirby\CLI\CLI;
use Kirby\Http\Remote;
use Kirby\Http\Url;
use Kirby\Toolkit\I18n;

return [
	'description' => 'Performs security checks of the site',
	'command' => static function (CLI $cli): void {
		$kirby        = $cli->kirby();
		$system       = $kirby->system();
		$updateStatus = $system->updateStatus();
		$messages     = [
			...array_column($updateStatus?->messages() ?? [], 'text'),
			...$updateStatus->exceptionMessages()
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
];

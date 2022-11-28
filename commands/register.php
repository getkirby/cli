<?php

declare(strict_types = 1);

use Kirby\CLI\CLI;

return [
	'description' => 'Registers the installation',
	'args' => [
		'email' => [
			'prefix'      => 'e',
			'longPrefix'  => 'email',
			'description' => 'The email address you’ve used to purchase the license',
		],
		'license' => [
			'prefix'      => 'l',
			'longPrefix'  => 'license',
			'description' => 'Your Kirby 3 license key',
		]
	],
	'command' => static function (CLI $cli): void {
		$kirby   = $cli->kirby();
		$license = $cli->argOrPrompt('license', 'Enter your license key:');
		$email   = $cli->argOrPrompt('email', 'Enter your email address:');

		$kirby->system()->register($license, $email);

		$cli->success('Your installation has been registered');
	}
];

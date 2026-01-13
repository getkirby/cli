<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands;

use Kirby\CLI\CLI;
use Kirby\CLI\Command;

class Register extends Command
{
	public static function args(): array
	{
		return [
			'email' => [
				'prefix'      => 'e',
				'longPrefix'  => 'email',
				'description' => 'The email address you\'ve used to purchase the license',
			],
			'license' => [
				'prefix'      => 'l',
				'longPrefix'  => 'license',
				'description' => 'Your Kirby 3 license key',
			]
		];
	}

	public static function command(CLI $cli): void
	{
		$kirby   = $cli->kirby();
		$license = $cli->argOrPrompt('license', 'Enter your license key:');
		$email   = $cli->argOrPrompt('email', 'Enter your email address:');

		$kirby->system()->register($license, $email);

		$cli->success('Your installation has been registered');
	}

	public static function description(): string|null
	{
		return 'Registers the installation';
	}
}

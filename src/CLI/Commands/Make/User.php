<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Make;

use Kirby\CLI\CLI;
use Kirby\CLI\Command;
use Kirby\Cms\User as UserModel;

class User extends Command
{
	public static function args(): array
	{
		return [
			'email' => [
				'description' => 'The email of the user'
			],
			'role' => [
				'description' => 'The role of the user'
			],
			'name' => [
				'description' => 'The name of the user'
			],
			'language' => [
				'description' => 'The language of the user',
			],
			'password' => [
				'description' => 'The password of the user'
			]
		];
	}

	public static function command(CLI $cli): void
	{
		$kirby    = $cli->kirby();
		$email    = $cli->argOrPrompt('email', 'Enter an email:');
		$password = $cli->argOrPrompt('password', 'Enter a password (Leave empty for passwordless login methods):', false);
		$role     = $cli->radio('Select a user role:', $kirby->roles()->pluck('id'))->prompt();
		$name     = $cli->argOrPrompt('name', 'Enter a name (optional):', false);
		$language = $cli->argOrPrompt('language', 'Enter a language code (Leave empty to use default EN):', false);

		$data = [
			'email'    => $email,
			'name'     => $name,
			'role'     => $role,
			'language' => empty($language) === false ? strtolower($language) : 'en'
		];

		if (empty($password) === false) {
			$data['password'] = $password;
		}

		// authenticate as almighty
		$kirby->impersonate('kirby');

		$user = UserModel::create($data);

		$cli->success('The user has been created. The new user id: ' . $user->id());
	}

	public static function description(): string|null
	{
		return 'Creates a new user';
	}
}

<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\UUID;

use Kirby\CLI\CLI;
use Kirby\Cms\ModelWithContent;
use Kirby\Uuid\Uuid;

class Duplicates
{
	protected static function check(CLI $cli, ModelWithContent $model, array &$uuids, array &$duplicates): void
	{
		$uuid = $model->content()->get('uuid');

		if ($uuid->isEmpty() === true) {
			return;
		}

		$uuid = $model->uuid()->toString();

		// the UUID already exists for another model
		if (isset($uuids[$uuid])) {
			// add this model and the one with the duplicate uuid
			// to the duplicates array to clean them up later
			$duplicates[] = $model;
			$duplicates[] = $uuids[$uuid];
		}

		$uuids[$uuid] = $model;
	}

	public static function command(CLI $cli): void
	{
		$kirby = $cli->kirby();
		$fix   = $cli->arg('fix');

		if ($fix === true) {
			$cli->confirmToContinue('Duplicate UUIDs will be removed and regenerated. This can break links in fields that use the UUID as reference. Do you want to continue?');
		}

		$uuids      = [];
		$duplicates = [];
		$site       = $kirby->site();

		$kirby->impersonate('kirby');

		// go through all site files
		foreach ($site->files() as $file) {
			static::check($cli, $file, $uuids, $duplicates);
		}

		// go through all pages
		foreach ($site->index(drafts: true) as $page) {
			static::check($cli, $page, $uuids, $duplicates);

			// go through all files
			foreach ($page->files() as $file) {
				static::check($cli, $file, $uuids, $duplicates);
			}
		}

		// go through all users
		foreach ($cli->kirby()->users() as $user) {
			static::check($cli, $user, $uuids, $duplicates);

			// go through all files
			foreach ($user->files() as $file) {
				static::check($cli, $file, $uuids, $duplicates);
			}
		}

		// remove duplicates from duplicates array
		$duplicates = array_unique($duplicates);

		if (count($duplicates) === 0) {
			$cli->success('There are no UUID duplicates');
			return;
		}

		// go through all collected models with duplicate UUIDs
		// and print info or fix them.
		foreach ($duplicates as $model) {
			$uuid = $model->uuid()->toString();

			if ($fix === true) {
				static::regenerate($model);
				$cli->out('✅ The duplicate UUID ' . $uuid . ' for ' . $model->id() . ' has been regenerated');
			} else {
				$cli->error('The UUID ' . $uuid . ' for ' . $model->id() . ' exists');
			}
		}

		if ($fix === true) {
			$cli->success(count($duplicates) . ' duplicates have been fixed');
			return;
		}

		$cli->out(count($duplicates) . ' duplicates! You can fix them with kirby uuid:duplicates --fix');
		exit(1);
	}

	protected static function regenerate(ModelWithContent $model): void
	{
		$model->uuid()->clear();
		$model->update([
			'uuid' => Uuid::generate()
		]);
		$model->uuid()->populate(true);
	}
}

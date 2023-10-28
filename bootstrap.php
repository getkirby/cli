<?php

namespace Kirby\CLI;

/**
 * Tries to load the kirby index.php
 */
function bootstrap(): string|null
{
	if ($index = index()) {
		// switch off the renderer to
		// avoid any output in the CLI
		$_ENV['KIRBY_RENDER'] = false;

		ob_start();
		require $index;
		ob_end_clean();

		// rendering could be useful in commands
		// again. That's why we switch it back on
		$_ENV['KIRBY_RENDER'] = true;
	}

	return $index;
}

/**
 * Returns the absolute path
 * to the Kirby index.php if it
 * can be found
 *
 * @return string|null
 */
function index(): string|null
{
	$locations = [
		'./',
		'./www',
		'./public',
		'./public_html'
	];

	foreach ($locations as $location) {
		// try to find the index.php in current working directory
		$index = realpath(getcwd() . '/' . $location . '/index.php');

		if ($index !== false) {
			return $index;
		}

		// try to find the index.php from the (possible) root of the project
		// in the __ROOT__ <- /vendor/getkirby/cli directory
		$index = realpath(dirname(__DIR__, 3) . '/' . $location . '/index.php');

		if ($index !== false) {
			return $index;
		}
	}

	return null;
}

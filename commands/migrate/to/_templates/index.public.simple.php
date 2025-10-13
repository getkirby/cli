<?php

use Kirby\Cms\App as Kirby;

require dirname(__DIR__) . '/kirby/bootstrap.php';

$kirby = new Kirby([
	'roots' => [
		'index'   => __DIR__,
		'base'    => $base = dirname(__DIR__),
		'site'    => $base . '/site',
		'content' => $base . '/content',
	]
]);

echo $kirby->render();

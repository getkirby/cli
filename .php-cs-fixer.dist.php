<?php

return Kirby\PhpCs\Config::create()->setFinder(
	PhpCsFixer\Finder::create()
		->exclude('node_modules')
		// scaffolding stubs, copied verbatim by `kirby make`
		->exclude('_templates')
		->in(__DIR__)
		->append([__DIR__ . '/bin/kirby'])
);

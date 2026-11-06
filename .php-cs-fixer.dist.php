<?php

return Kirby\PhpCs\Config::create()->setFinder(
	PhpCsFixer\Finder::create()
		->exclude('node_modules')
		// scaffolding stubs, copied verbatim by `kirby make`
		->exclude('_templates')
		// Kirby downloads cached by the test suite
		->exclude('tests/tmp')
		->in(__DIR__)
		->append([__DIR__ . '/bin/kirby'])
);

<?php

declare(strict_types = 1);

namespace Kirby\CLI;

use League\CLImate\Util\Writer\WriterInterface;

class QuietWriter implements WriterInterface
{
	public function write($content)
	{
		// be quiet here
	}
}

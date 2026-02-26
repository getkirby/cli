<?php

declare(strict_types = 1);

namespace Kirby\CLI;

use League\CLImate\Util\Writer\WriterInterface;

/**
 * Buffer writer that captures CLI output for test assertions
 */
class BufferWriter implements WriterInterface
{
	protected array $buffer = [];

	public function clear(): void
	{
		$this->buffer = [];
	}

	public function getLines(): array
	{
		return $this->buffer;
	}

	public function getOutput(): string
	{
		return implode('', $this->buffer);
	}

	public function write($content): void
	{
		$this->buffer[] = $content;
	}
}

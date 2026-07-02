<?php

declare(strict_types = 1);

namespace Kirby\CLI;

use PHPUnit\Framework\TestCase as BaseTestCase;

class TestCase extends BaseTestCase
{
	protected TestableCLI|null $cli = null;
	protected KirbyInstallation|null $kirbyInstallation = null;
	protected BufferWriter|null $outputBuffer = null;

	/**
	 * Asserts that the captured output contains the expected string
	 */
	protected function assertOutputContains(string $expected, string $message = ''): void
	{
		$output = $this->getOutput();
		$this->assertStringContainsString(
			$expected,
			$output,
			$message ?: "Expected output to contain '{$expected}'. Actual output: {$output}"
		);
	}

	/**
	 * Asserts that the captured output does not contain the expected string
	 */
	protected function assertOutputNotContains(string $expected, string $message = ''): void
	{
		$output = $this->getOutput();
		$this->assertStringNotContainsString(
			$expected,
			$output,
			$message ?: "Expected output not to contain '{$expected}'. Actual output: {$output}"
		);
	}

	/**
	 * Creates a TestableCLI instance with optional Kirby App
	 */
	protected function createCLI(
		object|null $kirby = null,
		array $roots = []
	): TestableCLI {
		$this->cli = new TestableCLI($kirby, $roots);
		return $this->cli;
	}

	/**
	 * Creates a TestableCLI instance with a real Kirby installation
	 */
	protected function createCLIWithKirby(
		string $version = 'main',
		array $content = []
	): TestableCLI {
		$kirby = $this->kirby($version, $content);
		return $this->createCLI($kirby);
	}

	/**
	 * Gets the captured output
	 */
	protected function getOutput(): string
	{
		if ($this->outputBuffer === null) {
			throw new \RuntimeException('Output capture not set up. Call setupOutputCapture() first.');
		}

		return $this->outputBuffer->getOutput();
	}

	/**
	 * Creates and returns a real Kirby App instance from a temp installation
	 */
	protected function kirby(
		string $version = 'main',
		array $content = []
	): object {
		$this->kirbyInstallation = new KirbyInstallation($version, $content);
		return $this->kirbyInstallation->app();
	}

	/**
	 * Returns the path to the temp Kirby installation
	 */
	protected function kirbyRoot(): string
	{
		if ($this->kirbyInstallation === null) {
			throw new \RuntimeException('No Kirby fixture. Call kirby() first.');
		}

		return $this->kirbyInstallation->root();
	}

	/**
	 * Sets up output capturing for the CLI instance
	 */
	protected function setupOutputCapture(TestableCLI|null $cli = null): BufferWriter
	{
		$cli ??= $this->cli;

		if ($cli === null) {
			throw new \RuntimeException('No CLI instance available. Create one first with createCLI()');
		}

		$this->outputBuffer = new BufferWriter();
		$cli->climate()->output->add('buffer', $this->outputBuffer);
		$cli->climate()->output->defaultTo('buffer');

		return $this->outputBuffer;
	}

	protected function tearDown(): void
	{
		// Clean up any Kirby fixture
		if ($this->kirbyInstallation !== null) {
			$this->kirbyInstallation->cleanup();
			$this->kirbyInstallation = null;

			// Kirby registers error handlers, restore to default
			restore_error_handler();
			restore_exception_handler();
		}

		$this->cli = null;
		$this->outputBuffer = null;
	}
}

<?php

declare(strict_types = 1);

namespace Kirby\CLI;

use Exception;
use Kirby\Cms\App;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * Manages Kirby CMS installations for testing
 *
 * Downloads and caches Kirby versions, bootstraps once per version,
 * and creates temporary content/site directories for each test.
 */
class KirbyInstallation
{
	protected \Kirby\Cms\App|null $app = null;
	protected string $tempDir;
	protected string $version;

	protected static string|null $baseDir = null;
	protected static string|null $bootstrappedVersion = null;
	protected static array $downloadedVersions = [];

	/**
	 * Creates a new temporary Kirby installation
	 *
	 * @param $version Git tag or 'main' for latest
	 * @param $content Array of content files to create (path => content)
	 */
	public function __construct(
		string $version = 'main',
		array $content = []
	) {
		$this->version = $version;

		$this->ensureBaseDir();
		$this->ensureKirbyDownloaded();
		$this->ensureBootstrapped();
		$this->tempDir = $this->createTempInstallation($content);
		$this->createApp();
	}

	/**
	 * Returns the App instance
	 */
	public function app(): App
	{
		return $this->app;
	}

	/**
	 * Cleans up the temporary installation
	 */
	public function cleanup(): void
	{
		// Remove temp directory
		if (is_dir($this->tempDir) === true) {
			$this->removeDirectory($this->tempDir);
		}

		// Destroy the Kirby App instance
		if ($this->app !== null) {
			App::destroy();
			$this->app = null;
		}
	}

	/**
	 * Clears the entire Kirby cache (useful for CI or manual cleanup)
	 */
	public static function clearCache(): void
	{
		if (static::$baseDir !== null && is_dir(static::$baseDir) === true) {
			static::removeDirectory(static::$baseDir);
			static::$baseDir = null;
			static::$downloadedVersions = [];
			static::$bootstrappedVersion = null;
		}
	}

	/**
	 * Creates the Kirby App instance with temp roots
	 */
	protected function createApp(): void
	{
		$this->app = new App([
			'roots' => [
				'index'   => $this->tempDir,
				'content' => $this->tempDir . '/content',
				'site'    => $this->tempDir . '/site',
				'kirby'   => $this->kirbyDir(),
			]
		]);
	}

	/**
	 * Creates a temporary directory for content and site
	 */
	protected function createTempInstallation(array $content = []): string
	{
		$tempDir = sys_get_temp_dir() . '/kirby-cli-test-' . uniqid();
		mkdir($tempDir, 0755, true);

		// Create required directories
		mkdir($tempDir . '/content', 0755, true);
		mkdir($tempDir . '/site', 0755, true);
		mkdir($tempDir . '/site/templates', 0755, true);

		// Create a basic site.txt for the home page
		mkdir($tempDir . '/content/home', 0755, true);
		file_put_contents($tempDir . '/content/home/default.txt', "Title: Home\n----\n");

		// Create a default template
		file_put_contents(
			$tempDir . '/site/templates/default.php',
			'<?php echo $page->title() ?>'
		);

		// Apply custom content
		foreach ($content as $path => $data) {
			$fullPath = $tempDir . '/content/' . $path;
			$dir = dirname($fullPath);
			if (is_dir($dir) === false) {
				mkdir($dir, 0755, true);
			}
			file_put_contents($fullPath, $data);
		}

		return $tempDir;
	}

	/**
	 * Downloads a specific Kirby version to the cache directory
	 */
	protected function downloadKirby(): void
	{
		$kirbyDir = $this->kirbyDir();

		if (is_dir($kirbyDir) === true) {
			static::$downloadedVersions[$this->version] = true;
			return;
		}

		// Build download URL based on version
		if ($this->version === 'main') {
			$zipUrl = 'https://github.com/getkirby/kirby/archive/refs/heads/main.zip';
			$extractedName = 'kirby-main';
		} else {
			$zipUrl = 'https://github.com/getkirby/kirby/archive/refs/tags/' . $this->version . '.zip';
			$extractedName = 'kirby-' . $this->version;
		}

		$zipFile = static::$baseDir . '/kirby-' . $this->version . '.zip';

		// Download
		$content = file_get_contents($zipUrl);
		if ($content === false) {
			throw new Exception('Failed to download Kirby ' . $this->version . ' from ' . $zipUrl);
		}
		file_put_contents($zipFile, $content);

		// Extract
		$zip = new \ZipArchive();
		if ($zip->open($zipFile) !== true) {
			throw new Exception('Failed to open Kirby zip file');
		}

		$zip->extractTo(static::$baseDir);
		$zip->close();

		// Rename extracted directory to version-specific name
		$extractedDir = static::$baseDir . '/' . $extractedName;
		if (is_dir($extractedDir) === true) {
			rename($extractedDir, $kirbyDir);
		}

		// Cleanup zip file
		unlink($zipFile);

		static::$downloadedVersions[$this->version] = true;
	}

	/**
	 * Ensures the base cache directory exists
	 */
	protected function ensureBaseDir(): void
	{
		if (static::$baseDir === null) {
			static::$baseDir = sys_get_temp_dir() . '/kirby-cli-cache';
			if (is_dir(static::$baseDir) === false) {
				mkdir(static::$baseDir, 0755, true);
			}
		}
	}

	/**
	 * Bootstraps Kirby once per version from the cache directory
	 */
	protected function ensureBootstrapped(): void
	{
		// Already bootstrapped this version
		if (static::$bootstrappedVersion === $this->version) {
			return;
		}

		// Cannot switch versions once bootstrapped (autoloader conflict)
		if (static::$bootstrappedVersion !== null) {
			throw new Exception(
				'Cannot switch Kirby versions within the same test run. ' .
				'Already bootstrapped version: ' . static::$bootstrappedVersion . ', ' .
				'requested version: ' . $this->version
			);
		}

		$bootstrapFile = $this->kirbyDir() . '/bootstrap.php';

		if (file_exists($bootstrapFile) === false) {
			throw new Exception('Kirby bootstrap.php not found at ' . $bootstrapFile);
		}

		require_once $bootstrapFile;
		static::$bootstrappedVersion = $this->version;
	}

	/**
	 * Ensures the Kirby version is downloaded
	 */
	protected function ensureKirbyDownloaded(): void
	{
		if (isset(static::$downloadedVersions[$this->version]) === false) {
			$this->downloadKirby();
		}
	}

	/**
	 * Returns the path to the cached Kirby directory for this version
	 */
	protected function kirbyDir(): string
	{
		return static::$baseDir . '/kirby-' . $this->version;
	}

	/**
	 * Removes a directory recursively (static version)
	 */
	protected static function removeDirectory(string $dir): void
	{
		if (is_dir($dir) === false) {
			return;
		}

		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
			RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ($iterator as $item) {
			if ($item->isDir() === true) {
				rmdir($item->getPathname());
			} else {
				unlink($item->getPathname());
			}
		}

		rmdir($dir);
	}

	/**
	 * Returns the path to the temp installation
	 */
	public function root(): string
	{
		return $this->tempDir;
	}

	/**
	 * Returns the Kirby version being used
	 */
	public function version(): string
	{
		return $this->version;
	}
}

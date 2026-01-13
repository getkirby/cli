<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands;

use Exception;
use GuzzleHttp\Client;
use Kirby\CLI\CLI;
use Kirby\CLI\Command;
use Throwable;

class Download extends Command
{
	public static function args(): array
	{
		return [
			'url' => [
				'description' => 'The URL to the file',
				'required' => true
			],
			'file' => [
				'description' => 'Where to save the download',
				'required' => true
			],
		];
	}

	public static function command(CLI $cli): void
	{
		$client   = new Client();
		$progress = $cli->progress()->total(100);
		$file     = $cli->arg('file');

		try {
			$response = $client->get($cli->arg('url'), [
				'progress' => function ($total, $downloaded) use ($cli, $progress): void {
					try {
						if ($total > 0 && $downloaded > 0) {
							$progress->total($total);
							$progress->current($downloaded, '');
						}
					} catch (Throwable $e) {
						$cli->out($e->getMessage());
					}
				},
			]);

			file_put_contents($file, (string)$response->getBody());
		} catch (Throwable $e) {
			throw new Exception('The file could not be downloaded. (Status: ' . $e->getResponse()->getStatusCode() . ')');
		}
	}

	public static function description(): string|null
	{
		return 'Downloads a file via URL';
	}
}

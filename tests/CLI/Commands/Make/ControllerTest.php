<?php

declare(strict_types = 1);

namespace Kirby\CLI\Commands\Make;

use Kirby\CLI\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(Controller::class)]
class ControllerTest extends TestCase
{
	public function testArgs(): void
	{
		$args = Controller::args();

		$this->assertArrayHasKey('name', $args);
		$this->assertSame('The name of the controller', $args['name']['description']);
	}

	public function testDescription(): void
	{
		$this->assertSame('Creates a new template controller in site/controllers', Controller::description());
	}
}

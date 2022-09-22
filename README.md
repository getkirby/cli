# Kirby CLI

The Kirby command line interface helps simplifying common tasks with your Kirby installations.

## Installation

```
composer global require getkirby/cli
```

## Available commands

```
- kirby clear:cache
- kirby clear:media
- kirby clear:sessions
- kirby download
- kirby help
- kirby install
- kirby install:kit
- kirby install:repo
- kirby make:blueprint
- kirby make:collection
- kirby make:command
- kirby make:config
- kirby make:controller
- kirby make:model
- kirby make:snippet
- kirby make:template
- kirby remove:command
- kirby unzip
- kirby version
```

## Listing commands

If you need a nice overview of all available commands you can simply run …

```
kirby
```

… without any additional arguments. This will not just show you the built-in commands, but also the globally and locally installed commands on your machine.

## Writing commands

You can create a new command via the CLI: 

```bash
kirby make:command hello
```

This will create a new `site/commands` folder in your installation with a new `hello.php` file

The CLI will already put the basic scaffolding into the file: 

```php
<?php

return [
    'description' => 'Nice command',
    'args' => [],
    'command' => static function ($cli): void {
        $cli->success('Nice command!');
    }
];
```

You can define your command logic in the command callback. The `$cli` object comes with a set of handy tools to create output, parse command arguments, create prompts and more. 

## Global commands

You might have some commands that you need for all your local Kirby installations. This is where global commands come in handy. You can create a new global command with the `--global` flag:

```bash
kirby make:command hello --global
```

The command file will then be place in `~/.kirby/commands/hello.php` and is automatically available everywhere. 

## Removing commands

Once you no longer need a command, you can remove it with …

```bash
kirby remove:command hello
```

If you have a local and a global command, you can choose which one to delete. 



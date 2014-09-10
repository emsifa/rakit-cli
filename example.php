<?php

require("vendor/autoload.php");

use Rakit\CLI\Command;
use Rakit\CLI\App;

$cli = new App($argv);

$cli->command("example:hello", function($console) {
	$console->writeln("Hello CLI");
});

$cli->command("example:argument", function($console) {
	$name = $console->argument("name", "Lorem");
	$console->writeln("Hello ".$name);
})
->addArgument("name", false);

$cli->command("example:option", function($console) {

	$caps = $console->option("caps");

	if($caps) {
		$console->writeln("HELLO CLI");
	} else {
		$console->writeln("hello cli");
	}

})->addOption("caps", Command::OPT_BOOLEAN, false);

$cli->run();
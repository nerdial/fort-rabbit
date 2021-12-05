<?php

use Symfony\Component\Console\Application;

$application = new Application();

// ... register commands

$application->add(new \App\Command\CraftPluginCommand());

$application->run();


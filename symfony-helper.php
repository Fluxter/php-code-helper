<?php

namespace Fluxter\SymfonyHelper;

require "vendor/autoload.php";
include "Commands/UpdateNamespaceCommand.php";

use Fluxter\SymfonyHelper\Commands\UpdateNamespaceCommand;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new UpdateNamespaceCommand());
$application->run();

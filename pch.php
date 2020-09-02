<?php

namespace Fluxter\PhpCodeHelper;

require "vendor/autoload.php";
include "Commands/UpdateNamespaceCommand.php";

use Fluxter\PhpCodeHelper\Commands\UpdateNamespaceCommand;
use Symfony\Component\Console\Application;

$application = new Application();
$application->add(new UpdateNamespaceCommand());
$application->run();

#!/usr/bin/env php
<?php

error_reporting(E_ALL);
set_time_limit(0);

require_once __DIR__ . '/../vendor/autoload.php';

use Application\Component\Console\Application;

putenv('SHELL_INTERACTIVE=true');

define('APPLICATION_ROOT', realpath(__DIR__ . '/..'));

$application = new Application();
$application->run();

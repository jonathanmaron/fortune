#!/usr/bin/env php
<?php

require __DIR__ . '/../vendor/autoload.php';

use Application\Component\Console\Application;

$application = new Application();
$application->run();

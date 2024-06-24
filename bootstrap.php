<?php
declare(strict_types=1);

// Report all PHP errors
error_reporting(E_ALL);

// Set the maximum execution time in seconds (0 means no limit)
set_time_limit(0);

// Set an environment variable indicating that the shell is interactive
putenv('SHELL_INTERACTIVE=true');

// Set the default locale for the application
locale_set_default('en_US');
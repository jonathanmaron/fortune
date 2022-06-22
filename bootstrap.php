<?php
declare(strict_types=1);

// phpcs:disable

error_reporting(E_ALL);

if (!defined('REQUEST_MICROTIME')) {
    define('REQUEST_MICROTIME', microtime(true));
}

ini_set('xdebug.var_display_max_depth', '999');

set_time_limit(0);

define('APPLICATION_ROOT', __DIR__);

putenv('SHELL_INTERACTIVE=true');

locale_set_default('en_US');

// phpcs:enable

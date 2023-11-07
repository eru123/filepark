<?php

define('__ROUTES__', __DIR__ . '/src/Routes');
define('__ROOT_DIR__', __DIR__);

require_once __DIR__ . '/vendor/autoload.php';

venv_protect();
venv_load(__DIR__ . '/.env', false);

if ($tz = venv('TZ')) {
    try {
        new DateTimeZone($tz);
    } catch (Exception $e) {
        throw new Exception('Invalid timezone: ' . $tz);
    }
}
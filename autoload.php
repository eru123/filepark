<?php

define('__ROUTES__', __DIR__ . '/src/Routes');
define('__ROOT_DIR__', __DIR__);
define('__PUBLIC_DIR__', __DIR__ . '/public');
define('__WRITABLE_DIR__', __DIR__ . '/writable');
define('__UPLOADS_DIR__', __WRITABLE_DIR__ . '/uploads');
define('__CACHE_DIR__', __WRITABLE_DIR__ . '/cache');
define('__LOGS_DIR__', __WRITABLE_DIR__ . '/logs');
define('__STORAGE_DIR__', __WRITABLE_DIR__ . '/storage');

require_once __DIR__ . '/vendor/autoload.php';

venv_protect();
venv_load(__DIR__ . '/.env', false);

if (venv('DEBUG')) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

if ($tz = venv('TZ')) {
    try {
        new DateTimeZone($tz);
    } catch (Exception $e) {
        throw new Exception('Invalid timezone: ' . $tz);
    }
}

if (
    venv('APP_ENV', 'production') == 'production' &&
    is_writable(dirname(__WRITABLE_DIR__)) &&
    !file_exists(__CACHE_DIR__ . '/writable_storage_check')
) {
    foreach ([__WRITABLE_DIR__, __UPLOADS_DIR__, __CACHE_DIR__, __LOGS_DIR__, __STORAGE_DIR__,] as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
    }
    file_put_contents(__CACHE_DIR__ . '/writable_storage_check', '');
} else if (
    venv('APP_ENV', 'production') != 'production' &&
    is_writable(dirname(__WRITABLE_DIR__))
) {
    foreach ([__WRITABLE_DIR__, __UPLOADS_DIR__, __CACHE_DIR__, __LOGS_DIR__, __STORAGE_DIR__,] as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }
    }
}

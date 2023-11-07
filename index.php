<?php

require_once __DIR__ . '/autoload.php';

use Filepark\Services\Domain;
use eru123\router\Router;

try {
    Domain::load_routes();
    Domain::run();
} catch (Throwable $e) {
    Router::status_page(500, 'Internal Server Error', $e->getMessage());
}
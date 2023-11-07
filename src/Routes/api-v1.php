<?php

use Filepark\Services\Domain;
use Filepark\Services\Response;
use eru123\router\Router;

$router = Domain::create(venv(['DOMAIN_API', 'DOMAIN']));

$v1 = new Router($router);
$v1->base('/v1');

$v1->fallback(fn() => Response::json([
    'status' => 404,
    'error' => 'Not Found',
], 404));

$router->child($v1);

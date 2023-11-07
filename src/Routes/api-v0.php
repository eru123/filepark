<?php

use Filepark\Services\Domain;
use Filepark\Services\Response;

$router = Domain::create(venv(['DOMAIN_API', 'DOMAIN']));
$router->base('/api');

$router->fallback(fn() => Response::json([
    'status' => 404,
    'error' => 'Not Found',
], 404));

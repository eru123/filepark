<?php

use Filepark\Services\Domain;
use eru123\router\Router;

$router = Domain::create(venv(['DOMAIN_API']));

$router->get('/$id/$name', 'Filepark\Controller\Api@stream');
$router->get('/$id/$token/$name', 'Filepark\Controller\Api@stream');

$router->fallback('Filepark\Controller\Api@no_resource');
$router->error('Filepark\Controller\Api@no_resource');

$api = new Router($router);
$api->base('/api');
$api->bootstrap(fn ($ctx) => header('Access-Control-Allow-Origin: *'));
$api->error('Filepark\Controller\Api@error');
$api->fallback('Filepark\Controller\Api@fallback');
$api->post('/upload', 'Filepark\Controller\Api@upload');

$router->child($api);

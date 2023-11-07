<?php

use Filepark\Services\Domain;

$router = Domain::create(venv(['DOMAIN_API', 'DOMAIN']));
$router->post('/upload', 'Filepark\Controller\Api@upload');

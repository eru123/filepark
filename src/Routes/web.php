<?php

use Filepark\Services\Domain;

$router = Domain::create(venv(['DOMAIN_WEB', 'DOMAIN']));
$router->static('/', __PUBLIC_DIR__, ['index.html'], function ($ctx) {
    
});

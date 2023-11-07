<?php

require_once __DIR__ . '/autoload.php';

use Filepark\Services\Domain;

Domain::load_routes();
Domain::run();
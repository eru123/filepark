<?php

namespace Filepark\Services;

class Response
{
    public static function code(int $code)
    {
        http_response_code($code);
    }

    public static function json($data, int $code = 200)
    {
        if (!headers_sent()) {
            header('Content-Type: application/json');
            self::code($code);
        }
        echo json_encode($data);
        exit;
    }

    public static function apiDebug($router) {
        
    }
}
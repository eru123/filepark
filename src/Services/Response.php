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

    public static function s3_file_stream($data, $expires = '+1 hour') {
        $size = $data['size'];
        $type = $data['type'];

        if (!headers_sent()) {
            header('Content-Type: ' . $type);
            header('Content-Length: ' . $size);
            header('Content-Disposition: inline; filename="' . $data['name'] . '"');
            header('Expires: ' . gmdate('D, d M Y H:i:s T', strtotime($expires)));
            header('Cache-Control: max-age=' . (strtotime($expires) - time()));
            header('Last-Modified: ' . gmdate('D, d M Y H:i:s T', strtotime($data['updated_at'])));
            static::code(200);
        }

        foreach (S3::get_stream($data['path']) as $chunk) {
            echo $chunk;
        }
        exit;
    }
}
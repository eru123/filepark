<?php

namespace Filepark\Controller;

use Filepark\Services\Response;
use Filepark\Services\S3;
use eru123\router\Context;
use eru123\orm\ORM;
use Exception;
use Throwable;

class Api
{
    public function error(Throwable $e)
    {
        return Response::json([
            'error' => $e->getMessage(),
            'trace' => $e->getTrace(),
        ], $e->getCode() ?: 500);
    }

    public function fallback(Context $ctx)
    {
        return Response::json([
            'error' => 'Resource not found',
        ], 404);
    }

    public function upload(Context $ctx)
    {
        $temporary = isset($_POST['temporary']) && ($_POST['temporary'] === true || intval($_POST['temporary']) === 1);
        $password = isset($_POST['password']) ? $_POST['password'] : null;
        $batch_id = isset($_POST['batch_id']) ? $_POST['batch_id'] : uniqid();
        $private = $password || (isset($_POST['private']) && ($_POST['private'] === true || intval($_POST['private']) === 1));

        $files = [];
        foreach ($_FILES as $v) {
            if (is_array($v['name'])) {
                foreach ($v['name'] as $i => $name) {
                    $files[] = [
                        'name' => $name,
                        'type' => $v['type'][$i],
                        'tmp_name' => $v['tmp_name'][$i],
                        'error' => $v['error'][$i],
                        'size' => $v['size'][$i],
                    ];
                }
            } else {
                $files[] = $v;
            }
        }

        if ($password) {
            $password = password_hash($password, PASSWORD_BCRYPT);
        }

        $datetime = date('Y-m-d H:i:s');
        $data = [];
        $last_id = null;

        foreach ($files as $file) {
            $name = $file['name'];
            $tmp_name = $file['tmp_name'];
            $size = $file['size'];
            $type = $file['type'];
            $error = $file['error'];

            if ($error !== UPLOAD_ERR_OK) {
                $msg = match ($error) {
                    UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
                    UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
                    UPLOAD_ERR_PARTIAL => 'The uploaded file was only partially uploaded',
                    UPLOAD_ERR_NO_FILE => 'No file was uploaded',
                    UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder',
                    UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
                    UPLOAD_ERR_EXTENSION => 'File upload stopped by extension',
                    default => 'Upload error with unknown reason',
                };
                throw new Exception($msg);
            }

            $hash = hash_file('sha256', $tmp_name);

            $filedata = [
                'name' => $name,
                'size' => $size,
                'type' => $type,
                'hash' => $hash,
                'path' => $hash,
                'private' => $private,
                'temporary' => $temporary,
                'batch_id' => $batch_id,
                'password' => $password,
                'created_at' => $datetime,
                'updated_at' => $datetime,
            ];

            $duplicate = ORM::select('uploads', [
                'where' => [
                    'hash' => $hash,
                    'size' => $size,
                ]
            ])?->exec()?->fetch();

            if (!$duplicate) {
                try {
                    $s3upload = S3::upload($hash, $tmp_name, [
                        'ACL' => $private ? 'private' : 'public-read',
                    ]);
                    $code = $s3upload->get('@metadata')['statusCode'];
                    if ($code !== 200) {
                        throw new Exception('Failed to upload file to cloud storage');
                    }
                } catch (Throwable $e) {
                    throw new Exception($e->getMessage());
                }
            }

            $orm = ORM::insert('uploads', $filedata);
            $stmt = $orm->exec();
            $id = $orm->id(null);
            if ($stmt->errorCode() !== '00000' || !$id || $id == $last_id) {
                throw new Exception('Failed to insert file data to database');
            }

            $last_id = $id;
            $data[] = [
                'id' => $id,
                'batch_id' => $batch_id,
                'name' => $name,
                'size' => $size,
                'url' => 'https://' . venv('DOMAIN') . "/$id",
                'stream' => venv('STREAM_BASE_URL'). "/$id/$name",
            ];
        }

        return Response::json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function stream(Context $ctx)
    {
        $id = isset($ctx->params['id']) ? $ctx->params['id'] : null;
        $token = isset($ctx->params['token']) ? $ctx->params['token'] : null;
        $name = isset($ctx->params['name']) ? urldecode($ctx->params['name']) : null;

        $upload = ORM::select('uploads', [
            'where' => [
                'id' => $id,
                'name'=> $name,
            ],
        ])?->exec()?->fetch();

        if (!$upload) {
            Response::code(404);
            exit;
        }

        if (isset($upload['private']) && $upload['private']) {
            $url = S3::url($upload['path'], '+2 hours', [
                'ResponseContentDisposition' => "inline; filename=\"{$upload['name']}\"",
                'ResponseContentType' => $upload['type'],
            ]);
            header('Location: ' . $url);
            exit;
        }

        Response::s3_file_stream($upload, '+1 hour');
    }

    public function no_resource($ctx) {
        Response::code(404);
        exit;
    }
}
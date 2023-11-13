<?php

namespace Filepark\Services;

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use Aws\Result;
use GuzzleHttp\Psr7\Stream;
use Exception;
use Throwable;
use DateTimeInterface;

class S3
{
    public static function s3(array $config = [])
    {
        $endpoint = venv('S3_HOST');
        $intr = [
            'credentials' => [
                'key' => venv('S3_KEY'),
                'secret' => venv('S3_SECRET'),
            ],
            'region' => venv('S3_REGION', 'us-east-1'),
            'version' => venv('S3_VERSION', 'latest'),
        ];
        if ($endpoint) {
            $intr['endpoint'] = $endpoint;
        }

        return new S3Client($config + $intr);
    }

    public static function upload(string $key, string $file, array $options = []): Result
    {
        try {
            $s3 = static::s3();
            $result = $s3->putObject([
                'Bucket' => venv('S3_BUCKET'),
                'Key' => $key,
                'SourceFile' => $file,
            ] + $options);

            $s3->waitUntil('ObjectExists', [
                'Bucket' => venv('S3_BUCKET'),
                'Key' => $key,
            ]);

            return $result;
        } catch (S3Exception $e) {
            throw new Exception($e->getMessage());
        } catch (Throwable $e) {
            throw new Exception($e->getMessage());
        }
    }

    public static function delete(string $key): Result
    {
        try {
            $s3 = static::s3();
            return $s3->deleteObject([
                'Bucket' => venv('S3_BUCKET'),
                'Key' => $key,
            ]);
        } catch (S3Exception $e) {
            throw new Exception($e->getMessage());
        } catch (Throwable $e) {
            throw new Exception($e->getMessage());
        }
    }

    public static function get(string $key): Result
    {
        try {
            $s3 = static::s3();
            return $s3->getObject([
                'Bucket' => venv('S3_BUCKET'),
                'Key' => $key,
            ]);
        } catch (S3Exception $e) {
            throw new Exception($e->getMessage());
        } catch (Throwable $e) {
            throw new Exception($e->getMessage());
        }
    }

    public static function get_stream(string $key, int $buffer_size = 1024)
    {
        try {
            $s3 = static::s3();
            $result = $s3->getObject([
                'Bucket' => venv('S3_BUCKET'),
                'Key' => $key,
                '@http' => [
                    'stream' => true,
                ],
            ]);
            $stream = $result['Body'];
            if (!$stream instanceof Stream) {
                throw new Exception('Failed to get stream');
            }
            if ($stream->isSeekable()) {
                $stream->rewind();
            }
            while (!$stream->eof()) {
                yield $stream->read($buffer_size);
            }
        } catch (S3Exception $e) {
            throw new Exception($e->getMessage());
        } catch (Throwable $e) {
            throw new Exception($e->getMessage());
        }
    }

    public static function exists(string $key): bool
    {
        try {
            $s3 = static::s3();
            return $s3->doesObjectExist(venv('S3_BUCKET'), $key);
        } catch (S3Exception $e) {
            throw new Exception($e->getMessage());
        } catch (Throwable $e) {
            throw new Exception($e->getMessage());
        }
    }

    public static function url(string $key, DateTimeInterface|int|string $expires = '+30 minutes', array $opts = []): string
    {
        try {
            $s3 = static::s3();
            $cmd = $s3->getCommand('GetObject', [
                'Bucket' => venv('S3_BUCKET'),
                'Key' => $key,
            ] + $opts);
            $request = $s3->createPresignedRequest($cmd, $expires);
            return (string) $request->getUri();
        } catch (S3Exception $e) {
            throw new Exception($e->getMessage());
        } catch (Throwable $e) {
            throw new Exception($e->getMessage());
        }
    }

    public static function public_url(string $key): string
    {
        try {
            $s3 = static::s3();
            return $s3->getObjectUrl(venv('S3_BUCKET'), $key);
        } catch (S3Exception $e) {
            throw new Exception($e->getMessage());
        } catch (Throwable $e) {
            throw new Exception($e->getMessage());
        }
    }

    public static function list(string $prefix = null): array
    {
        try {
            $s3 = static::s3();
            $result = $s3->listObjects([
                'Bucket' => venv('S3_BUCKET'),
                'Prefix' => $prefix,
            ]);
            return $result->get('Contents');
        } catch (S3Exception $e) {
            throw new Exception($e->getMessage());
        } catch (Throwable $e) {
            throw new Exception($e->getMessage());
        }
    }

    public static function copy(string $key, string $newKey): Result
    {
        try {
            $s3 = static::s3();
            return $s3->copyObject([
                'Bucket' => venv('S3_BUCKET'),
                'Key' => $newKey,
                'CopySource' => venv('S3_BUCKET') . '/' . $key,
            ]);
        } catch (S3Exception $e) {
            throw new Exception($e->getMessage());
        } catch (Throwable $e) {
            throw new Exception($e->getMessage());
        }
    }
}
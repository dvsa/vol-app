<?php

declare(strict_types=1);

namespace Dvsa\Olcs\DocumentShare\Service;

use Aws\S3\Exception\S3Exception;
use Dvsa\Olcs\DocumentShare\Data\Object\File;
use Laminas\Http\Response;

/**
 * Shared S3 object streaming for the native document store and the bucket browser. Keeps the read
 * contract (404/NoSuchKey -> false, any other error -> logged + rethrown) and the write contract
 * (200 on success, 500 on failure) in one place so the two services can't drift.
 *
 * Using classes must expose `$s3Client` (Aws\S3\S3Client), `$bucket` (string) and `$logger`
 * (Psr\Log\LoggerInterface) properties.
 */
trait StreamsS3Objects
{
    private const READ_CHUNK_SIZE = 8192;

    /**
     * GetObject at an absolute $key and stream its body into a temp-file-backed File. Returns false
     * only when the object does not exist (404/NoSuchKey) or a temp file can't be opened; any other
     * S3 error is logged and rethrown rather than masquerading as "not found". The caller decides
     * how to treat a zero-byte body.
     */
    protected function streamObjectToFile(string $key): File|false
    {
        try {
            $result = $this->s3Client->getObject(['Bucket' => $this->bucket, 'Key' => $key]);
        } catch (S3Exception $e) {
            if ($e->getStatusCode() === 404 || $e->getAwsErrorCode() === 'NoSuchKey') {
                return false;
            }

            $this->logger->error('Failed to read object from S3', ['key' => $key, 'error' => $e->getAwsErrorMessage()]);

            throw $e;
        }

        $file = new File();
        $destination = fopen($file->getResource(), 'wb');

        if ($destination === false) {
            $this->logger->error('Failed to open temp file for S3 download', ['key' => $key]);
            return false;
        }

        try {
            $body = $result['Body'];
            while (!$body->eof()) {
                $chunk = $body->read(self::READ_CHUNK_SIZE);
                if ($chunk === '') {
                    break;
                }
                fwrite($destination, $chunk);
            }
        } finally {
            fclose($destination);
        }

        return $file;
    }

    /**
     * Stream a File's contents to S3 via PutObject at an absolute $key. Returns a Response: 200 on
     * success, 500 on a read/open failure or an S3 error.
     */
    protected function putFileToObject(string $key, File $file): Response
    {
        $response = new Response();
        $handle = fopen($file->getResource(), 'rb');

        if ($handle === false) {
            $this->logger->error('Failed to open file for upload to S3', ['key' => $key]);
            $response->setStatusCode(500);
            $response->setContent('Unable to open file for reading');
            return $response;
        }

        try {
            $this->s3Client->putObject([
                'Bucket' => $this->bucket,
                'Key' => $key,
                'Body' => $handle,
                'ContentType' => $file->getMimeType(),
            ]);
            $response->setStatusCode(200);
        } catch (S3Exception $e) {
            $this->logger->error('Failed to write object to S3', ['key' => $key, 'error' => $e->getAwsErrorMessage()]);
            $response->setStatusCode(500);
            $response->setContent((string) $e->getAwsErrorMessage());
        } finally {
            if (is_resource($handle)) {
                @fclose($handle);
            }
        }

        return $response;
    }
}

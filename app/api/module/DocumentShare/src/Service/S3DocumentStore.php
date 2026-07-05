<?php

declare(strict_types=1);

namespace Dvsa\Olcs\DocumentShare\Service;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client as AwsS3Client;
use Dvsa\Olcs\DocumentShare\Data\Object\File;
use Laminas\Http\Response;
use Psr\Log\LoggerInterface;

/**
 * Native S3 implementation of the document store.
 *
 * Keys are derived from the stored document identifier via {@see normaliseKey()}: leading
 * slashes are stripped (identifiers are stored without one, but template/image reads pass
 * "/templates/...") and a configurable prefix is applied so the keys align with whatever the
 * EBS->S3 sync produced — alignment is configuration, not code.
 */
class S3DocumentStore implements DocumentStoreInterface
{
    use StreamsS3Objects;

    public function __construct(
        protected AwsS3Client $s3Client,
        protected string $bucket,
        protected string $keyPrefix,
        protected LoggerInterface $logger
    ) {
    }

    /**
     * @param string $path
     * @throws \Exception
     */
    #[\Override]
    public function read($path): File | false
    {
        $file = $this->streamObjectToFile($this->normaliseKey($path));

        // The document store treats an empty object as "not present" (matches the legacy WebDAV
        // store's contract).
        if ($file === false || $file->getSize() === 0) {
            return false;
        }

        return $file;
    }

    /**
     * @param string $path
     */
    #[\Override]
    public function write($path, File $file): Response
    {
        return $this->putFileToObject($this->normaliseKey($path), $file);
    }

    /**
     * S3 PutObject overwrites an existing object in place, so for the native S3 store update is
     * identical to write (unlike the WebDAV store, which distinguishes create from overwrite).
     *
     * @param string $path
     */
    #[\Override]
    public function update($path, File $file): Response
    {
        return $this->putFileToObject($this->normaliseKey($path), $file);
    }

    /**
     * S3 DeleteObject is idempotent — it succeeds whether or not the key exists — so a
     * successful call always maps to 200. (The bucket is not versioned, so this is a true
     * delete.) Returning a Response (rather than a bool) satisfies the isOk()/isNotFound()
     * contract that RemoveDeletedDocuments relies on.
     *
     * @param string $path
     */
    #[\Override]
    public function remove($path, $hard = false): Response
    {
        $key = $this->normaliseKey($path);
        $response = new Response();

        try {
            $this->s3Client->deleteObject([
                'Bucket' => $this->bucket,
                'Key' => $key,
            ]);
            $response->setStatusCode(200);
        } catch (S3Exception $e) {
            $this->logger->error('Failed to delete document from S3', [
                'key' => $key,
                'error' => $e->getAwsErrorMessage(),
            ]);
            $response->setStatusCode(500);
            $response->setContent((string) $e->getAwsErrorMessage());
        }

        return $response;
    }

    private function normaliseKey($path): string
    {
        $key = ltrim((string) $path, '/');

        if ($this->keyPrefix !== '') {
            return trim($this->keyPrefix, '/') . '/' . $key;
        }

        return $key;
    }
}

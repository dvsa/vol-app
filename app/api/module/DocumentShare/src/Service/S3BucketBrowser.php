<?php

declare(strict_types=1);

namespace Dvsa\Olcs\DocumentShare\Service;

use Aws\S3\Exception\S3Exception;
use Aws\S3\S3Client as AwsS3Client;
use Dvsa\Olcs\DocumentShare\Data\Object\File;
use Laminas\Http\Response;
use Psr\Log\LoggerInterface;

/**
 * S3 browser for the super-admin document-store browser.
 *
 * Operates RELATIVE to a configurable root prefix: incoming prefixes/keys are relative to the root,
 * the root is prepended before talking to S3, and it is stripped from results — so the UI presents a
 * filesystem rooted at the document store (e.g. 'migration/olcs') rather than the raw bucket root.
 * An empty root = the whole bucket. S3 is the source of truth, decoupled from the `document` table.
 */
class S3BucketBrowser
{
    use StreamsS3Objects;

    public const DEFAULT_MAX_KEYS = 100;

    protected string $rootPrefix;

    public function __construct(
        protected AwsS3Client $s3Client,
        protected string $bucket,
        string $rootPrefix,
        protected LoggerInterface $logger
    ) {
        $this->rootPrefix = trim($rootPrefix, '/');
    }

    /**
     * List one "folder" level under $prefix (relative to the root) using a delimiter, returning
     * sub-folders (CommonPrefixes) and objects with keys relative to the root. Forward-paginated.
     *
     * @return array{prefix:string, folders:string[], objects:array<int,array{key:string,size:int,lastModified:mixed}>, nextContinuationToken:?string, isTruncated:bool}
     */
    public function listByPrefix(
        string $prefix = '',
        ?string $continuationToken = null,
        int $maxKeys = self::DEFAULT_MAX_KEYS
    ): array {
        $this->assertBucketConfigured();
        $this->assertSafeKey($prefix);
        $absolutePrefix = $this->toAbsolute($prefix);

        $params = [
            'Bucket' => $this->bucket,
            'Delimiter' => '/',
            'MaxKeys' => $maxKeys,
        ];
        if ($absolutePrefix !== '') {
            $params['Prefix'] = $absolutePrefix;
        }
        if (!empty($continuationToken)) {
            $params['ContinuationToken'] = $continuationToken;
        }

        try {
            $result = $this->s3Client->listObjectsV2($params);
        } catch (S3Exception $e) {
            $this->logger->error('Failed to list S3 bucket', ['prefix' => $absolutePrefix, 'error' => $e->getAwsErrorMessage()]);
            throw $e;
        }

        $folders = [];
        foreach (($result['CommonPrefixes'] ?? []) as $commonPrefix) {
            $folders[] = $this->toRelative($commonPrefix['Prefix']);
        }

        $objects = [];
        foreach (($result['Contents'] ?? []) as $object) {
            // Skip the zero-byte "folder placeholder" object whose key equals the prefix itself.
            if (($object['Key'] ?? null) === $absolutePrefix) {
                continue;
            }
            $objects[] = [
                'key' => $this->toRelative($object['Key']),
                'size' => (int) ($object['Size'] ?? 0),
                'lastModified' => $object['LastModified'] ?? null,
            ];
        }

        return [
            'prefix' => $prefix,
            'folders' => $folders,
            'objects' => $objects,
            'nextContinuationToken' => $result['NextContinuationToken'] ?? null,
            'isTruncated' => (bool) ($result['IsTruncated'] ?? false),
        ];
    }

    /**
     * Fetch an object by its key (relative to the root), streamed into a temp-file-backed File. A
     * real object (including a legitimately zero-byte one) is returned; false means it does not
     * exist (404/NoSuchKey). Any other S3 error is logged and rethrown rather than being masked as
     * "not found".
     */
    public function getObject(string $key): File|false
    {
        $this->assertBucketConfigured();
        $this->assertSafeKey($key);

        return $this->streamObjectToFile($this->toAbsolute($key));
    }

    /**
     * Overwrite/create an object at a key (relative to the root). Phase 2 — used only when the
     * S3_BUCKET_BROWSER_OVERWRITE toggle is on. Does NOT touch the document table (raw bucket tool).
     */
    public function putObject(string $key, File $file): Response
    {
        $this->assertBucketConfigured();
        $this->assertSafeKey($key);

        return $this->putFileToObject($this->toAbsolute($key), $file);
    }

    /**
     * Prepend the configured root prefix to a key/prefix that is relative to it.
     */
    private function toAbsolute(string $relative): string
    {
        $relative = ltrim($relative, '/');

        return $this->rootPrefix === '' ? $relative : $this->rootPrefix . '/' . $relative;
    }

    /**
     * Strip the configured root prefix from an absolute S3 key/prefix.
     */
    private function toRelative(string $absolute): string
    {
        if ($this->rootPrefix === '') {
            return $absolute;
        }

        $root = $this->rootPrefix . '/';

        return str_starts_with($absolute, $root) ? substr($absolute, strlen($root)) : $absolute;
    }

    /**
     * Fail closed when the S3 bucket isn't configured. This runs at call time (not at construction)
     * so the feature-toggle and system-admin gates get a chance to reject the request first.
     */
    private function assertBucketConfigured(): void
    {
        if ($this->bucket === '') {
            throw new \RuntimeException('Missing required option document_share.s3.bucket');
        }
    }

    /**
     * Guard against path-traversal style keys/prefixes before any S3 call. An empty prefix is the
     * root and is allowed.
     */
    private function assertSafeKey(string $key): void
    {
        if ($key === '') {
            return;
        }

        // Reject NUL/control characters (never valid in our keys, and they would break the
        // Content-Disposition header and log lines) and absolute keys. A literal '"' is allowed
        // here (a legal S3 key char) and is escaped at the header layer instead, so
        // legitimately-named objects stay browsable.
        if (preg_match('/[\x00-\x1f\x7f]/', $key) === 1 || str_starts_with($key, '/')) {
            throw new \InvalidArgumentException(sprintf('Unsafe S3 key or prefix: "%s"', $key));
        }

        // Reject '.'/'..' only as whole path segments — a literal '..' inside a name (e.g.
        // 'report..final.pdf') is a valid S3 key and must stay browsable/downloadable.
        foreach (explode('/', $key) as $segment) {
            if ($segment === '.' || $segment === '..') {
                throw new \InvalidArgumentException(sprintf('Unsafe S3 key or prefix: "%s"', $key));
            }
        }
    }
}

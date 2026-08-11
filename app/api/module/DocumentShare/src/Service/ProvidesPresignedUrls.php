<?php

declare(strict_types=1);

namespace Dvsa\Olcs\DocumentShare\Service;

/**
 * Optional document-store capability: mint a short-lived, pre-authenticated URL that reads an
 * object directly (e.g. an S3 presigned GET), so a caller can fetch the bytes without the store
 * proxying them. WebDAV has no equivalent, so it does not implement this — callers must feature
 * detect with `instanceof` and fall back to streaming.
 */
interface ProvidesPresignedUrls
{
    /**
     * A presigned GET URL for the stored object, valid for $ttlSeconds.
     */
    public function presignedGetUrl(string $identifier, int $ttlSeconds): string;
}

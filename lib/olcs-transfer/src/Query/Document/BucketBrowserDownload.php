<?php

namespace Dvsa\Olcs\Transfer\Query\Document;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Super-admin S3 bucket browser: download an object by its raw S3 key (not a document id).
 * Extends AbstractDownload to inherit the stream/inline + logger-omit-response behaviour.
 *
 * @Transfer\RouteName("backend/document/bucket-browser/download")
 */
class BucketBrowserDownload extends AbstractDownload
{
    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     */
    protected $key;

    /**
     * Raw S3 object key
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }
}

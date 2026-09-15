<?php

namespace Dvsa\Olcs\Transfer\Query\Document;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Super-admin S3 bucket browser: list one "folder" level (delimiter-grouped) under a prefix.
 * Listing comes straight from S3 (source of truth), forward-paginated by a continuation token —
 * there is intentionally no paged/ordered query interface (S3 has no total count or offset paging).
 *
 * @Transfer\RouteName("backend/document/bucket-browser")
 */
class BucketBrowserList extends AbstractQuery
{
    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Optional
     */
    protected $prefix;

    /**
     * @var string
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Optional
     */
    protected $continuationToken;

    /**
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @return string
     */
    public function getContinuationToken()
    {
        return $this->continuationToken;
    }
}

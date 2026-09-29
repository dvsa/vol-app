<?php

namespace Dvsa\Olcs\Transfer\Query\Document;

use Dvsa\Olcs\Transfer\FieldType\Traits\ApplicationOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\LicenceOptional;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedTrait;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * List document analyses, optionally scoped to an application (new or variation), a licence
 * or a single document.
 *
 * Paged and ordered like DocumentList, so the result is always one bounded page rather than
 * the whole table: page, limit, sort and order carry no Optional annotation and are therefore
 * required inputs, and limit is capped by PagedTrait's allowed values. That keeps every scope
 * genuinely optional without the handler having to insist on one.
 *
 * Application and licence use the shared field traits so they are digit-validated the same
 * way as on DocumentList.
 *
 * @Transfer\RouteName("backend/document/analysis-list")
 */
class DocumentAnalysisList extends AbstractQuery implements OrderedQueryInterface, PagedQueryInterface
{
    use OrderedTrait;
    use PagedTrait;
    use ApplicationOptional;
    use LicenceOptional;

    /**
     * @Transfer\Optional
     */
    protected $document;

    /**
     * @Transfer\Optional
     */
    protected $status;


    public function getDocument()
    {
        return $this->document;
    }

    public function getStatus()
    {
        return $this->status;
    }
}

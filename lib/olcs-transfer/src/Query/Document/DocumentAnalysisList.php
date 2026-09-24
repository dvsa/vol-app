<?php

namespace Dvsa\Olcs\Transfer\Query\Document;

use Dvsa\Olcs\Transfer\FieldType\Traits\ApplicationOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\LicenceOptional;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * List document analyses, scoped to an application (new or variation) or a licence.
 *
 * Application and licence use the shared field traits so they are digit-validated the same
 * way as on DocumentList.
 *
 * @Transfer\RouteName("backend/document/analysis-list")
 */
class DocumentAnalysisList extends AbstractQuery
{
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

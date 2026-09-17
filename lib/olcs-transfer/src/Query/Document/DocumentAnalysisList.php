<?php

namespace Dvsa\Olcs\Transfer\Query\Document;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/document/analysis-list")
 */
class DocumentAnalysisList extends AbstractQuery
{
    protected $application;

    public function getApplication()
    {
        return $this->application;
    }
}

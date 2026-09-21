<?php

namespace Dvsa\Olcs\Transfer\Query\Document;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/document/analysis-list")
 */
class DocumentAnalysisList extends AbstractQuery
{
    /**
     * @Transfer\Optional
     */
    protected $application;

    /**
     * @Transfer\Optional
     */
    protected $document;

    /**
     * @Transfer\Optional
     */
    protected $status;

    public function getApplication()
    {
        return $this->application;
    }

    public function getDocument()
    {
        return $this->document;
    }

    public function getStatus()
    {
        return $this->status;
    }
}

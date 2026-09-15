<?php

namespace Dvsa\Olcs\Transfer\Query\Document;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/document/download")
 */
class Download extends AbstractDownload
{
    /**
     * @var  int
     * @Transfer\Filter("Laminas\Filter\Digits")
     */
    protected $identifier;

    /**
     * Get file identifier
     *
     * @return int
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }
}

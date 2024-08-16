<?php

namespace Dvsa\Olcs\Api\Domain\Query\Bookmark;

/**
 * PreviousPublicationByLicence
 */
class PreviousPublicationByLicence extends PreviousPublication
{
    protected $licence;

    /**
     * @return int
     */
    public function getLicence()
    {
        return $this->licence;
    }
}

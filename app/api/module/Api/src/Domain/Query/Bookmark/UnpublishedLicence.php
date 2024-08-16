<?php

namespace Dvsa\Olcs\Api\Domain\Query\Bookmark;

/**
 * Unpublished Licence
 */
class UnpublishedLicence extends Unpublished
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

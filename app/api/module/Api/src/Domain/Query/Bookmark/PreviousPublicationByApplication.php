<?php

namespace Dvsa\Olcs\Api\Domain\Query\Bookmark;

/**
 * PreviousPublicationByApplication
 */
class PreviousPublicationByApplication extends PreviousPublication
{
    protected $application;

    /**
     * @return int
     */
    public function getApplication()
    {
        return $this->application;
    }
}

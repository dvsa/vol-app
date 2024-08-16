<?php

namespace Dvsa\Olcs\Api\Domain\Query\Bookmark;

/**
 * Unpublished Application
 */
class UnpublishedApplication extends Unpublished
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

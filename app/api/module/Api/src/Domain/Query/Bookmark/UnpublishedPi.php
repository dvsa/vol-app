<?php

namespace Dvsa\Olcs\Api\Domain\Query\Bookmark;

/**
 * Unpublished Pi
 */
class UnpublishedPi extends Unpublished
{
    protected $pi;

    /**
     * @return mixed
     */
    public function getPi()
    {
        return $this->pi;
    }
}

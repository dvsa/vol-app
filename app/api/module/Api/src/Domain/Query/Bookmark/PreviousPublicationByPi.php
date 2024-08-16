<?php

namespace Dvsa\Olcs\Api\Domain\Query\Bookmark;

/**
 * PreviousPublicationByPi
 */
class PreviousPublicationByPi extends PreviousPublication
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

<?php

namespace Dvsa\Olcs\Api\Domain\Query\Bookmark;

/**
 * Unpublished Bus Reg
 */
class UnpublishedBusReg extends Unpublished
{
    protected $busReg;

    /**
     * @return int
     */
    public function getBusReg()
    {
        return $this->busReg;
    }
}

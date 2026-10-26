<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

/**
 * Trait Irhp Permit Range Is Reserve
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
trait IrhpPermitRangeSsReserve
{
    /**
     *
     * @var int
     */
    #[Transfer\Validator('Laminas\Validator\Digits')]
    #[Transfer\Validator('Laminas\Validator\GreaterThan', options: ['min' => -1])]
    #[Transfer\Optional]
    protected $ssReserve;

    /**
     * @return int
     */
    public function getSsReserve()
    {
        return $this->ssReserve;
    }
}

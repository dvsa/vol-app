<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

/**
 * Trait Irhp Permit Range Is Lost Replacement
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
trait IrhpPermitRangeIsLostReplacement
{
    /**
     *
     *  @var int
     */
    #[Transfer\Validator('Laminas\Validator\Digits')]
    #[Transfer\Validator('Laminas\Validator\GreaterThan', options: ['min' => -1])]
    #[Transfer\Optional]
    protected $lostReplacement;

    /**
     * @return int
     */
    public function getIsLostReplacement()
    {
        return $this->lostReplacement;
    }
}

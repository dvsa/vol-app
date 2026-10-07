<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;
/**
 * IrhpPermitRangeTo
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
trait IrhpPermitRangeTo
{
    /**
     *
     * @var int
     */
    #[Transfer\Validator('Laminas\Validator\Digits')]
    #[Transfer\Validator('Laminas\Validator\GreaterThan', options: ['min' => 0])]
    #[Transfer\Optional]
    protected $toNo;

    /**
     * @return int
     */
    public function getToNo(): int
    {
        return $this->toNo;
    }
}

<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait Cases
 *
 * Only called Cases because Case is a reserved word. Still works as if it was called Case.
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Valtech <uk@valtech.co.uk>
 */
trait Prohibition
{
    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $prohibition;

    /**
     * @return int
     */
    public function getProhibition()
    {
        return $this->prohibition;
    }
}

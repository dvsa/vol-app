<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait SubCategory
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
trait SubCategory
{
    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $subCategory;

    /**
     * @return int
     */
    public function getSubCategory()
    {
        return $this->subCategory;
    }
}

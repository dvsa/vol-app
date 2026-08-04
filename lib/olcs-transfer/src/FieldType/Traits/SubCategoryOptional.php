<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait SubCategoryOptional
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
trait SubCategoryOptional
{
    /**
     * @var int
     * @Transfer\Optional
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

<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait Category Optional
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Andy Newton <andy@vitri.ltd>
 */
trait CategoryOptional
{
    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     * @Transfer\Optional
     */
    protected $category;

    /**
     * @return int
     */
    public function getCategory()
    {
        return $this->category;
    }
}

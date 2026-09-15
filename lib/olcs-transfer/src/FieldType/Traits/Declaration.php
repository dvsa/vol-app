<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait Declaration
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Andy Newton <andy@vitri.ltd>
 */
trait Declaration
{
    /**
     * @var int
     * @Transfer\Optional
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\Between", options={"min": 0, "max": 1})
     */
    protected $declaration;

    /**
     * @return int
     */
    public function getDeclaration()
    {
        return $this->declaration;
    }
}

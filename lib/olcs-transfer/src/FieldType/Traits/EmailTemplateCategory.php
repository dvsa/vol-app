<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Trait EmailTemplateCategory
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Andy Newton <andy@vitri.ltd>
 */
trait EmailTemplateCategory
{
    /**
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": -1})
     * @Transfer\Optional
     * @var int
     */
    protected $emailTemplateCategory;

    /**
     * @return int
     */
    public function getEmailTemplateCategory()
    {
        return $this->emailTemplateCategory;
    }
}

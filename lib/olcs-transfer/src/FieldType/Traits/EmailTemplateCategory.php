<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

/**
 * Trait EmailTemplateCategory
 *
 * @package Dvsa\Olcs\Transfer\Command\Traits\FieldType
 * @author Andy Newton <andy@vitri.ltd>
 */
trait EmailTemplateCategory
{
    /**
     * @var int
     */
    #[Transfer\Validator('Laminas\Validator\Digits')]
    #[Transfer\Validator('Laminas\Validator\GreaterThan', options: ['min' => -1])]
    #[Transfer\Optional]
    protected $emailTemplateCategory;

    /**
     * @return int
     */
    public function getEmailTemplateCategory()
    {
        return $this->emailTemplateCategory;
    }
}

<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

/**
 * Master Template Optional
 */
trait MasterTemplateOptional
{
    /**
     * @var int
     */
    #[Transfer\Optional]
    #[Transfer\Filter('Laminas\Filter\Digits')]
    #[Transfer\Validator('Laminas\Validator\Digits')]
    #[Transfer\Validator('Laminas\Validator\GreaterThan', options: ['min' => 0])]
    protected $masterTemplate;

    /**
     * @return int
     */
    public function getMasterTemplate()
    {
        return $this->masterTemplate;
    }
}

<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

/**
 * Master Template
 */
trait MasterTemplate
{
    /**
     * @var int
     */
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

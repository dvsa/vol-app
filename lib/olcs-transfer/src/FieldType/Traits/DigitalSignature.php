<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;
trait DigitalSignature
{
    /**
     * @var int
     */
    #[Transfer\Filter('Laminas\Filter\Digits')]
    #[Transfer\Validator('Laminas\Validator\Digits')]
    #[Transfer\Validator('Laminas\Validator\GreaterThan', options: ['min' => 0])]
    protected $digitalSignature;

    /**
     * @return int
     */
    public function getDigitalSignature()
    {
        return $this->digitalSignature;
    }
}

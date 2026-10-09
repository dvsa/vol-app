<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

/**
 * FromInternal
 */
trait FromInternal
{
    /**
     * @var int
     */
    #[Transfer\Filter('Laminas\Filter\Digits')]
    #[Transfer\Validator('Laminas\Validator\Digits')]
    #[Transfer\Validator('Laminas\Validator\GreaterThan', options: ['min' => -1])]
    protected $fromInternal = 0;

    /**
     * @return int
     */
    public function getFromInternal()
    {
        return $this->fromInternal;
    }
}

<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

trait LastLoggedInFrom
{
    #[Transfer\Filter('Laminas\Filter\StringTrim')]
    #[Transfer\Validator('Date', options: ['format' => 'Y-m-d'])]
    protected $lastLoggedInFrom;

    /**
     * @return string
     */
    public function getLastLoggedInFrom()
    {
        return $this->lastLoggedInFrom;
    }
}

<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

trait IsMlhOptional
{
    #[Transfer\Optional]
    #[Transfer\Filter('Laminas\Filter\StringTrim')]
    #[Transfer\Validator('Laminas\Validator\InArray', options: ['haystack' => ['Y', 'N']])]
    protected $isMlh;

    public function getIsMlh()
    {
        return $this->isMlh;
    }
}

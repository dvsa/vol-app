<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

trait ExcludeLimitedReadOnlyOptional
{
    #[Transfer\Optional]
    #[Transfer\Filter('Laminas\Filter\Boolean')]
    protected $excludeLimitedReadOnly;

    public function getExcludeLimitedReadOnly()
    {
        return $this->excludeLimitedReadOnly;
    }
}

<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;
trait IsInternalOptional
{
    #[Transfer\Optional]
    #[Transfer\Filter('Laminas\Filter\Boolean')]
    protected $isInternal;

    public function getIsInternal()
    {
        return $this->isInternal;
    }
}

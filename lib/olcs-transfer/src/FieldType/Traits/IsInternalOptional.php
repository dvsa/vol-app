<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

trait IsInternalOptional
{
    /**
     * @Transfer\Optional
     * @Transfer\Filter("Laminas\Filter\Boolean")
     */
    protected $isInternal;

    public function getIsInternal()
    {
        return $this->isInternal;
    }
}

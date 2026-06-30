<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

trait Version
{
    /**
     * @var int
     * @Transfer\Filter("Laminas\Filter\Digits")
     * @Transfer\Validator("Laminas\Validator\Digits")
     * @Transfer\Validator("Laminas\Validator\GreaterThan", options={"min": 0})
     */
    protected $version;

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }
}

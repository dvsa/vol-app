<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

trait TrafficAreasOptionalWithOther
{
    /**
     * @Transfer\Optional
     * @Transfer\ArrayInput
     * @Transfer\ArrayFilter("Dvsa\Olcs\Transfer\Filter\UniqueItems")
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={
     *     "haystack": {"B","C","D","F","G","H","K","M","N","all","other"}
     *  })
     */
    protected $trafficAreas = [];

    /**
     * @return array
     */
    public function getTrafficAreas()
    {
        return $this->trafficAreas;
    }
}

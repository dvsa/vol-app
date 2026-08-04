<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Traffic Areas
 */
trait TrafficAreasOptional
{
    /**
     * @Transfer\Optional
     * @Transfer\ArrayInput
     * @Transfer\ArrayFilter("Dvsa\Olcs\Transfer\Filter\UniqueItems")
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={
     *      "haystack": {"B","C","D","F","G","H","K","M","N","all"}
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

<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Traffic Areas
 */
trait TrafficAreas
{
    /**
     * @Transfer\ArrayInput
     * @Transfer\ArrayValidator("Laminas\Validator\NotEmpty")
     * @Transfer\ArrayFilter("Dvsa\Olcs\Transfer\Filter\UniqueItems")
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray", options={
     *      "haystack": {"B","C","D","F","G","H","K","M","N"}
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

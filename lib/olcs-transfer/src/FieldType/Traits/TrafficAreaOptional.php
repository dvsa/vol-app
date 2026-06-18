<?php

namespace Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Traffic Area
 */
trait TrafficAreaOptional
{
    /**
     * @var String
     * @Transfer\Filter("Laminas\Filter\StringTrim")
     * @Transfer\Validator("Laminas\Validator\InArray",
     *      options={
     *          "haystack": {"B","C","D","F","G","H","K","M","N"}}
     *      )
     * @Transfer\Optional
     */
    protected $trafficArea = null;

    /**
     * @return string
     */
    public function getTrafficArea()
    {
        return $this->trafficArea;
    }
}

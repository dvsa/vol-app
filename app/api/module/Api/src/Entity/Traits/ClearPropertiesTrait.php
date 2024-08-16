<?php

namespace Dvsa\Olcs\Api\Entity\Traits;

/**
 * ClearPropertiesTrait
 */
trait ClearPropertiesTrait
{
    /**
     * Clear properties
     *
     * @param array $properties array of properties
     *
     * @return void
     */
    public function clearProperties(array $properties = []): void
    {
        foreach ($properties as $property) {
            if (property_exists($this, $property)) {
                $this->$property = null;
            }
        }
    }
}

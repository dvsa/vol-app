<?php

namespace Dvsa\Olcs\Api\Entity\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * ClearPropertiesWithCollectionsTrait
 */
trait ClearPropertiesWithCollectionsTrait
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
                $newProperty = null;

                if ($this->$property instanceof Collection) {
                    $newProperty = new ArrayCollection();
                }

                $this->$property = $newProperty;
            }
        }
    }
}

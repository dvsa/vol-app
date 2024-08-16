<?php

namespace Dvsa\OlcsTest\Api\Entity\Traits;

use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesWithCollectionsTrait;

class StubClearPropertiesWithCollectionsTrait
{
    use ClearPropertiesWithCollectionsTrait;

    public $property;

    public function setProperty($property)
    {
        $this->property = $property;

        return $this;
    }

    public function getProperty()
    {
        return $this->property;
    }
}

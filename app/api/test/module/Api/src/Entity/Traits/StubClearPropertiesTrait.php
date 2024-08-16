<?php

namespace Dvsa\OlcsTest\Api\Entity\Traits;

use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesTrait;

class StubClearPropertiesTrait
{
    use ClearPropertiesTrait;

    private $property;

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

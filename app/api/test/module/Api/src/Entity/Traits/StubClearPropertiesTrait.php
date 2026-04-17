<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Traits;

use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesTrait;

class StubClearPropertiesTrait
{
    use ClearPropertiesTrait;

    private $property;

    public function setProperty(mixed $property): mixed
    {
        $this->property = $property;

        return $this;
    }

    public function getProperty(): mixed
    {
        return $this->property;
    }
}

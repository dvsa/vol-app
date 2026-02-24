<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Entity\Traits;

use Dvsa\Olcs\Api\Entity\Traits\ClearPropertiesWithCollectionsTrait;

class StubClearPropertiesWithCollectionsTrait
{
    use ClearPropertiesWithCollectionsTrait;

    public $property;

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

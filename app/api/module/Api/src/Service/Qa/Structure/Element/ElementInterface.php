<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element;

interface ElementInterface
{
    /**
     * Get the representation of this element to be returned by the API endpoint
     *
     * @return array
     */
    public function getRepresentation();
}

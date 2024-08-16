<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt;

class EmissionsCategoryFactory
{
    /**
     * Create and return an EmissionsCategory instance
     *
     * @param string $type
     * @param int|null $value
     * @param int $permitsRemaining
     *
     * @return EmissionsCategory
     */
    public function create($type, $value, $permitsRemaining)
    {
        return new EmissionsCategory($type, $value, $permitsRemaining);
    }
}

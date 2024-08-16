<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral;

class StandardAndCabotageFactory
{
    /**
     * Create and return a StandardAndCabotage instance
     *
     * @param string|null $value
     *
     * @return StandardAndCabotage
     */
    public function create($value)
    {
        return new StandardAndCabotage($value);
    }
}

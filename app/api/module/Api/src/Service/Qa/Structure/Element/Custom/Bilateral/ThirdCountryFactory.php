<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral;

class ThirdCountryFactory
{
    /**
     * Create and return a ThirdCountry instance
     *
     * @param string|null $yesNo
     *
     * @return ThirdCountry
     */
    public function create($yesNo)
    {
        return new ThirdCountry($yesNo);
    }
}

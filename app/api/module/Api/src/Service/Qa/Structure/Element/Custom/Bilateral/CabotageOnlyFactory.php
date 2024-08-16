<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral;

class CabotageOnlyFactory
{
    /**
     * Create and return a CabotageOnly instance
     *
     * @param string|null $yesNo
     * @param string $countryName
     *
     * @return CabotageOnly
     */
    public function create($yesNo, $countryName)
    {
        return new CabotageOnly($yesNo, $countryName);
    }
}

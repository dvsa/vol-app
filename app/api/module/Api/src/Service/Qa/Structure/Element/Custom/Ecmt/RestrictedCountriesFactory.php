<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt;

class RestrictedCountriesFactory
{
    /**
     * Create and return a RestrictedCountries instance
     *
     * @param bool|null $yesNo
     * @param string $questionKey
     *
     * @return RestrictedCountries
     */
    public function create($yesNo, $questionKey)
    {
        return new RestrictedCountries($yesNo, $questionKey);
    }
}

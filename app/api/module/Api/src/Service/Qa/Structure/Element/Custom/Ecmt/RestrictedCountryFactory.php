<?php

namespace Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt;

class RestrictedCountryFactory
{
    /**
     * Create and return a RestrictedCountry instance
     *
     * @param string $code
     * @param string $labelTranslationKey
     * @param bool checked
     *
     * @return RestrictedCountry
     */
    public function create($code, $labelTranslationKey, $checked)
    {
        return new RestrictedCountry($code, $labelTranslationKey, $checked);
    }
}

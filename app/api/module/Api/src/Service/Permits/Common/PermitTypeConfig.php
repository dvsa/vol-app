<?php

namespace Dvsa\Olcs\Api\Service\Permits\Common;

class PermitTypeConfig
{
    /**
     * Create instance
     *
     * @param string $restrictedCountriesQuestionKey
     *
     * @return PermitTypeConfig
     */
    public function __construct(private $restrictedCountriesQuestionKey, private readonly array $restrictedCountryIds)
    {
    }

    /**
     * Return the restricted countries question translation key
     *
     * @return string
     */
    public function getRestrictedCountriesQuestionKey()
    {
        return $this->restrictedCountriesQuestionKey;
    }

    /**
     * Return the restricted country ids
     *
     * @return array
     */
    public function getRestrictedCountryIds()
    {
        return $this->restrictedCountryIds;
    }
}

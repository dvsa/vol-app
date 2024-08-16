<?php

namespace Dvsa\Olcs\Api\Service\Permits\ApplyRanges;

class RestrictedCountryIdsProvider
{
    /**
     * Create service instance
     *
     *
     * @return RestrictedCountryIdsProvider
     */
    public function __construct(private readonly array $restrictedCountryIds)
    {
    }

    /**
     * Return a list of ids representing the restricted countries
     *
     * @return array
     */
    public function getIds()
    {
        return $this->restrictedCountryIds;
    }
}

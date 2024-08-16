<?php

namespace Dvsa\Olcs\Api\Service\Permits\Common;

use RuntimeException;

class TypeBasedPermitTypeConfigProvider
{
    /**
     * Create service instance
     *
     *
     * @return TypeBasedPermitTypeConfigProvider
     */
    public function __construct(private array $config)
    {
    }

    /**
     * Return the restricted country codes corresponding to the specified IrhpPermitType
     *
     * @param int $irhpPermitTypeId
     * @param array $excludedRestrictedCountryIds
     *
     * @return PermitTypeConfig
     *
     * @throws RuntimeException
     */
    public function getPermitTypeConfig($irhpPermitTypeId, $excludedRestrictedCountryIds = [])
    {
        $typesConfig = $this->config['permits']['types'];

        if (!isset($typesConfig[$irhpPermitTypeId])) {
            throw new RuntimeException('No config found for permit type ' . $irhpPermitTypeId);
        }

        $typeConfig = $typesConfig[$irhpPermitTypeId];

        $restrictedCountryIds = array_diff($typeConfig['restricted_country_ids'], $excludedRestrictedCountryIds);

        return new PermitTypeConfig(
            $typeConfig['restricted_countries_question_key'],
            $restrictedCountryIds
        );
    }
}

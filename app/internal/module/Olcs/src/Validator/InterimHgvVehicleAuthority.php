<?php

namespace Olcs\Validator;

class InterimHgvVehicleAuthority extends InterimVehicleAuthority
{
    const TOT_AUTH_VEHICLES_KEY = 'totAuthHgvVehicles';
    const HGV_VEHICLE_AUTHORITY_EXCEEDED = 'hgvVehicleAuthExceeded';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = array(
        self::VEHICLE_AUTHORITY_EXCEEDED => 'The interim vehicle authority cannot exceed the total vehicle authority',
        self::HGV_VEHICLE_AUTHORITY_EXCEEDED => 'The interim Heavy Goods Vehicle Authority cannot exceed the total Heavy Goods Vehicle Authority',
        self::VALUE_BELOW_ONE => 'The input is not greater or equal than \'1\'',
    );

    /**
     * Get vehicle authority exceeded error key
     *
     * @param array|null $context Context
     *
     * @return string
     */
    protected function getVehicleAuthorityExceededErrorKey(?array $context): string
    {
        return !empty($context['isEligibleForLgv']) ? self::HGV_VEHICLE_AUTHORITY_EXCEEDED : self::VEHICLE_AUTHORITY_EXCEEDED;
    }
}

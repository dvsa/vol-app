<?php

namespace Olcs\Validator;

class InterimLgvVehicleAuthority extends InterimVehicleAuthority
{
    const TOT_AUTH_VEHICLES_KEY = 'totAuthLgvVehicles';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $messageTemplates = array(
        self::VEHICLE_AUTHORITY_EXCEEDED => 'The interim Light Goods Vehicle Authority cannot exceed the total Light Goods Vehicle Authority',
        self::VALUE_BELOW_ONE            => 'The input is not greater or equal than \'1\'',
    );
}

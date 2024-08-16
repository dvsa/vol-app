<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Traits;

use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\RefData;

/**
 * Derived type of licence params trait
 */
trait DerivedTypeOfLicenceParamsTrait
{
    /**
     * Derive the correct vehicle type ref data for the application/licence based upon information in the command
     *
     * @param string|null $vehicleType
     * @param string $operatorType
     *
     * @return string
     */
    public function getDerivedVehicleType($vehicleType, $operatorType)
    {
        if (
            $vehicleType == RefData::APP_VEHICLE_TYPE_LGV ||
            $vehicleType == RefData::APP_VEHICLE_TYPE_MIXED
        ) {
            return $vehicleType;
        }

        $mappings = [
            Licence::LICENCE_CATEGORY_GOODS_VEHICLE => RefData::APP_VEHICLE_TYPE_HGV,
            Licence::LICENCE_CATEGORY_PSV => RefData::APP_VEHICLE_TYPE_PSV,
        ];

        return $mappings[$operatorType];
    }

    /**
     * Derive the correct operator type ref data for the application/licence based upon information in the command
     *
     * @param string $operatorType
     * @param string $niFlag
     *
     * @return string
     */
    public function getDerivedOperatorType($operatorType, $niFlag)
    {
        if ($niFlag !== 'Y') {
            return $operatorType;
        }

        return Licence::LICENCE_CATEGORY_GOODS_VEHICLE;
    }
}

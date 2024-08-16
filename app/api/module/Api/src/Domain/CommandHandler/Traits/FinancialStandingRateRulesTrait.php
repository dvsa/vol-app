<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Traits;

use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\FinancialStandingRate as Entity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Financial standing rate rules trait
 */
trait FinancialStandingRateRulesTrait
{
    /**
     * Verify that the specified vehicle type logically matches other inputs, and throw a ValidationException if
     * this is not the case
     *
     *
     * @throws ValidationException
     */
    private function checkInputRules(CommandInterface $command)
    {
        $licenceType = $command->getLicenceType();
        $goodsOrPsv = $command->getGoodsOrPsv();
        $vehicleType = $command->getVehicleType();

        $msg = null;
        if (
            $licenceType == Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL &&
            $goodsOrPsv == Licence::LICENCE_CATEGORY_GOODS_VEHICLE
        ) {
            if (!in_array($vehicleType, [Entity::VEHICLE_TYPE_HGV, Entity::VEHICLE_TYPE_LGV])) {
                $msg = 'Vehicle type must be HGV or LGV for standard international goods licence';
            }
        } else {
            if ($vehicleType != Entity::VEHICLE_TYPE_NOT_APPLICABLE) {
                $msg = 'Vehicle type must be Not Applicable for licences other than standard international/goods';
            }
        }

        if ($msg) {
            throw new ValidationException([$msg]);
        }
    }
}

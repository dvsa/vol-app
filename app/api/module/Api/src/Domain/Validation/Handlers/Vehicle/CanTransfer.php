<?php

/**
 * Can Transfer
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Vehicle;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;

/**
 * Can Transfer
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CanTransfer extends AbstractHandler
{
    /**
     * @inheritdoc
     */
    public function isValid($dto)
    {
        // Can access source licence
        if ($this->canAccessLicence($dto->getId()) === false) {
            return false;
        }

        // Can access target licence
        if ($this->canAccessLicence($dto->getTarget()) === false) {
            return false;
        }

        foreach ($dto->getLicenceVehicles() as $id) {
            if ($this->canAccessLicenceVehicle($id) === false) {
                return false;
            }
        }

        return true;
    }
}

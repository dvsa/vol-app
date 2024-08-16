<?php

/**
 * Can Access Licence Vehicles With Ids
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;

/**
 * Can Access Licence Vehicles With Ids
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CanAccessLicenceVehiclesWithIds extends AbstractHandler
{
    /**
     * @inheritdoc
     */
    public function isValid($dto)
    {
        foreach ($dto->getIds() as $id) {
            if ($this->canAccessLicenceVehicle($id) === false) {
                return false;
            }
        }

        return true;
    }
}

<?php

/**
 * Modify
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Vehicle\Application;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessLicenceVehicleWithId;

/**
 * Modify
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Modify extends CanAccessLicenceVehicleWithId
{
    /**
     * @inheritdoc
     */
    #[\Override]
    public function isValid($dto)
    {
        if ($this->canAccessApplication($dto->getApplication()) === false) {
            return false;
        }

        return parent::isValid($dto);
    }
}

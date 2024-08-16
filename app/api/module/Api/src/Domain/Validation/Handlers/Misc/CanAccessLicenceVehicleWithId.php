<?php

/**
 * Can Access Licence Vehicle With Id
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;

/**
 * Can Access Licence Vehicle With Id
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CanAccessLicenceVehicleWithId extends AbstractHandler
{
    /**
     * @inheritdoc
     */
    public function isValid($dto)
    {
        return $this->canAccessLicenceVehicle($dto->getId());
    }
}

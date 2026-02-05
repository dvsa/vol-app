<?php

/**
 * Modify List
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Vehicle\Application;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc\CanAccessLicenceVehiclesWithIds;

/**
 * Modify List
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ModifyList extends CanAccessLicenceVehiclesWithIds
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

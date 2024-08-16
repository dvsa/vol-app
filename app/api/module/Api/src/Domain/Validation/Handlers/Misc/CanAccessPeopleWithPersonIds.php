<?php

/**
 * Can Access People With Person Ids
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;

/**
 * Can Access People With Person Ids
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CanAccessPeopleWithPersonIds extends AbstractHandler
{
    /**
     * @inheritdoc
     */
    public function isValid($dto)
    {
        foreach ($dto->getPersonIds() as $id) {
            if ($this->canAccessPerson($id) === false) {
                return false;
            }
        }

        return true;
    }
}

<?php

/**
 * Can Access Organisation Person With Ids
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;

/**
 * Can Access Organisation Person With Ids
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CanAccessOrganisationPersonWithIds extends AbstractHandler
{
    /**
     * @inheritdoc
     */
    public function isValid($dto)
    {
        foreach ($dto->getIds() as $id) {
            if ($this->canAccessOrganisationPerson($id) === false) {
                return false;
            }
        }

        return true;
    }
}

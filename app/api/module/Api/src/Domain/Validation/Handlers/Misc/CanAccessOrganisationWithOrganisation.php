<?php

/**
 * Can Access Organisation With Organisation
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

/**
 * Can Access Organisation With Organisation
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CanAccessOrganisationWithOrganisation extends CanAccessOrganisationWithId
{
    #[\Override]
    protected function getId($dto)
    {
        return $dto->getOrganisation();
    }
}

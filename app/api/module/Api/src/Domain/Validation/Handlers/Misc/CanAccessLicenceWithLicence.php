<?php

/**
 * Can Access Licence With Licence
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

/**
 * Can Access Licence With Licence
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CanAccessLicenceWithLicence extends CanAccessLicenceWithId
{
    #[\Override]
    protected function getId($dto)
    {
        return $dto->getLicence();
    }
}

<?php

/**
 * Can Access Licence Operating Centre With Id
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;

/**
 * Can Access Licence Operating Centre With Id
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CanAccessLicenceOperatingCentreWithId extends AbstractHandler
{
    /**
     * @inheritdoc
     */
    public function isValid($dto)
    {
        return $this->canAccessLicenceOperatingCentre($this->getId($dto));
    }

    protected function getId($dto)
    {
        return $dto->getId();
    }
}

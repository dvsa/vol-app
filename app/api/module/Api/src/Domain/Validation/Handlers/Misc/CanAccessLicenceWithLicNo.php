<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;

/**
 * Can Access Licence With Licence number eg OB1234567
 */
class CanAccessLicenceWithLicNo extends AbstractHandler
{
    /**
     * @inheritdoc
     */
    public function isValid($dto)
    {
        return $this->canAccessLicence($dto->getLicenceNumber());
    }
}

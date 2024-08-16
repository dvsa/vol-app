<?php

/**
 * Can Access Xoc With Reference
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\OperatingCentre;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;

/**
 * Can Access Xoc With Reference
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CanAccessXocWithReference extends AbstractHandler
{
    /**
     * @inheritdoc
     */
    public function isValid($dto)
    {
        [$prefix, $id] = $this->splitTypeAndId($dto->getId());

        if ($prefix === 'A') {
            return $this->canAccessApplicationOperatingCentre($id);
        }

        if ($prefix === 'L') {
            return $this->canAccessLicenceOperatingCentre($id);
        }

        return false;
    }

    private function splitTypeAndId($ref)
    {
        $type = substr((string) $ref, 0, 1);

        $id = (int)substr((string) $ref, 1);

        return [$type, $id];
    }
}

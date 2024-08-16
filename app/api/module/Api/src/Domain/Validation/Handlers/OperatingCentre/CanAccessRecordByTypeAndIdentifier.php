<?php

/**
 * Can Access Record By Type And Identifier
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\OperatingCentre;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;

/**
 * Can Access Record By Type And Identifier
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CanAccessRecordByTypeAndIdentifier extends AbstractHandler
{
    /**
     * @inheritdoc
     */
    public function isValid($dto)
    {
        if ($dto->getType() === 'licence') {
            return $this->canAccessLicence($dto->getIdentifier());
        }

        return $this->canAccessApplication($dto->getIdentifier());
    }
}

<?php

/**
 * Can Access Application or Licence With Id
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Workshop;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;

/**
 * Can Access Application or Licence With Id
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CanAccessApplicationOrLicenceWithId extends AbstractHandler
{
    /**
     * @inheritdoc
     */
    public function isValid($dto)
    {
        $applicationId = $this->getApplication($dto);
        if ($applicationId) {
            return $this->canAccessApplication($applicationId);
        }
        $licenceId = $this->getLicence($dto);
        if ($licenceId) {
            return $this->canAccessLicence($licenceId);
        }

        return false;
    }

    protected function getApplication($dto)
    {
        return $dto->getApplication();
    }

    protected function getLicence($dto)
    {
        return $dto->getLicence();
    }
}

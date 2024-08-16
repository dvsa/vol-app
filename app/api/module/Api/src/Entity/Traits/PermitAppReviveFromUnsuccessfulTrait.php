<?php

namespace Dvsa\Olcs\Api\Entity\Traits;

use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Entity\IrhpInterface;
use Dvsa\Olcs\Api\Entity\System\RefData;

/**
 * Permit app revive from unsuccessful trait
 */
trait PermitAppReviveFromUnsuccessfulTrait
{
    /**
     * Whether the permit application can be revived from an unsuccessful state
     *
     * @return bool
     */
    public function canBeRevivedFromUnsuccessful()
    {
        return $this->status->getId() == IrhpInterface::STATUS_UNSUCCESSFUL;
    }

    /**
     * Revive this application from an unsuccessful state
     *
     *
     * @throws ForbiddenException
     */
    public function reviveFromUnsuccessful(RefData $underConsiderationStatus)
    {
        if (!$this->canBeRevivedFromUnsuccessful()) {
            throw new ForbiddenException('Unable to revive this application from an unsuccessful state');
        }

        $this->status = $underConsiderationStatus;
    }
}

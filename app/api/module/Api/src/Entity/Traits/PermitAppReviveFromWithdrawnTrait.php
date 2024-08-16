<?php

namespace Dvsa\Olcs\Api\Entity\Traits;

use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\WithdrawableInterface;

/**
 * Permit app revive from withdrawn trait
 */
trait PermitAppReviveFromWithdrawnTrait
{
    /**
     * Whether the permit application can be revived from a withdrawn state
     *
     * @return bool
     */
    public function canBeRevivedFromWithdrawn()
    {
        if (!$this->getInScope() || !$this->isWithdrawn()) {
            return false;
        }

        $permittedReasons = [
            WithdrawableInterface::WITHDRAWN_REASON_UNPAID,
            WithdrawableInterface::WITHDRAWN_REASON_DECLINED,
        ];

        return in_array(
            $this->withdrawReason->getId(),
            $permittedReasons
        );
    }

    /**
     * Revive this application from a withdrawn state
     *
     *
     * @throws ForbiddenException
     */
    public function reviveFromWithdrawn(RefData $underConsiderationStatus)
    {
        if (!$this->canBeRevivedFromWithdrawn()) {
            throw new ForbiddenException('Unable to revive this application from a withdrawn state');
        }

        $this->status = $underConsiderationStatus;
        $this->withdrawReason = null;
        $this->withdrawnDate = null;
    }
}

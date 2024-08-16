<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Fee;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;

/**
 * CanPayOutstandingFees
 */
class CanPayOutstandingFees extends AbstractHandler
{
    /**
     * @param \Dvsa\Olcs\Transfer\Command\Transaction\PayOutstandingFees $dto
     *
     * @return bool
     */
    public function isValid($dto)
    {
        if ($dto->getOrganisationId()) {
            return $this->canAccessOrganisation($dto->getOrganisationId());
        }

        if ($dto->getApplicationId()) {
            return $this->canAccessApplication($dto->getApplicationId());
        }

        if ($dto->getIrhpApplication()) {
            return $this->canAccessIrhpApplicationWithId($dto->getIrhpApplication());
        }

        if ($dto->getFeeIds()) {
            $valid = true;
            foreach ($dto->getFeeIds() as $feeId) {
                $valid = $valid && $this->canAccessFee($feeId);
            }
            return $valid;
        }

        return false;
    }
}

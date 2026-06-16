<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Entity\Fee\Fee;

/**
 * Can Access Fee
 */
class CanAccessFee extends AbstractCanAccessEntity
{
    protected $repo = 'Fee';

    #[\Override]
    public function isValid($entityId)
    {
        if (!parent::isValid($entityId)) {
            return false;
        }
        $dto = $this->getDto();
        $licenceId = $dto->getLicenceId();
        $applicationId = $dto->getApplicationId();
        if ($licenceId === null && $applicationId === null) {
            return true;
        }

        /** @var Fee $fee */
        $fee = $this -> getEntity($entityId);
        if ($licenceId !== null) {
            return $fee->getLicence() ?->getId() === $licenceId;
        }
        if ($applicationId !== null) {
            return $fee->getApplication() ?->getId() === $applicationId;
        }
    }
}

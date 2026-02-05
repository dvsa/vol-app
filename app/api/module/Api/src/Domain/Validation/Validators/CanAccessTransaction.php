<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

/**
 * Can Access Transaction
 */
class CanAccessTransaction extends AbstractCanAccessEntity
{
    protected $repo = 'Transaction';

    /**
     * Override this method, to get transaction using reference
     *
     * @param mixed $entityId Transation reference or ID
     *
     * @return \Dvsa\Olcs\Api\Entity\Fee\Transaction
     */
    #[\Override]
    protected function getEntity($entityId)
    {
        // is entityId is numeric then assume it is a transaction ID
        if (is_numeric($entityId)) {
            return $this->getRepo($this->repo)->fetchById($entityId);
        }

        // if not numeric assume it is a transaction reference
        return $this->getRepo($this->repo)->fetchByReference($entityId);
    }
}

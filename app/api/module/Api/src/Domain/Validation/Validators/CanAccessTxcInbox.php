<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Entity\Bus\LocalAuthority;
use Dvsa\Olcs\Api\Entity\Ebsr\TxcInbox;

/**
 * Can access TxcInbox
 */
class CanAccessTxcInbox extends AbstractDoesOwnEntity
{
    protected $repo = 'Bus';

    /**
     * Is valid
     *
     * @param int|null $entityId Entity id
     *
     * @return bool
     */
    #[\Override]
    public function isValid($entityId)
    {
        if ($entityId === null) {
            return false;
        }

        if ($this->isOperator()) {
            return parent::isValid($entityId);
        }

        if ($this->isLocalAuthority()) {
            return true;
        }

        return false;
    }
}

<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

/**
 * Can Update TxcInbox record
 */
class CanUpdateTxcInbox extends AbstractCanAccessEntity
{
    protected $repo = 'User';

    /**
     * Is Valid, yes for at least one integer indexed entity id array and internal users or local authorities
     *
     * @param array $entityIds Array of entity ids to update
     *
     * @return bool
     */
    #[\Override]
    public function isValid($entityIds)
    {
        if (empty($entityIds)) {
            return false;
        }

        if ($this->isInternalUser() || $this->isLocalAuthority()) {
            return true;
        }

        return false;
    }
}

<?php

/**
 * Abstract Can Access Entity
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

/**
 * Abstract Can Access Entity
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractCanAccessEntity extends AbstractDoesOwnEntity
{
    #[\Override]
    public function isValid($entityId)
    {
        if ($this->isInternalUser()) {
            return true;
        }

        if ($this->isSystemUser()) {
            return true;
        }

        return parent::isValid($entityId);
    }
}

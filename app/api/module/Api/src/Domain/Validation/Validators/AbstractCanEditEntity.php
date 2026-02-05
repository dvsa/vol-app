<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Entity\User\Permission;

/**
 * Abstract Can Edit Entity
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
abstract class AbstractCanEditEntity extends AbstractDoesOwnEntity
{
    #[\Override]
    public function isValid($entityId)
    {
        if ($this->isGranted(Permission::INTERNAL_EDIT)) {
            return true;
        }

        if ($this->isSystemUser()) {
            return true;
        }

        return parent::isValid($entityId);
    }
}

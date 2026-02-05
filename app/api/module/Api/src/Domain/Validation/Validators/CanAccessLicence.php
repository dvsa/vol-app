<?php

/**
 * Can Access Licence
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

/**
 * Can Access Licence
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CanAccessLicence extends AbstractCanAccessEntity
{
    protected $repo = 'Licence';

    /**
     * Get Licence entity
     *
     * @param mixed $entityId Licence ID or licNo
     *
     * @return \Dvsa\Olcs\Api\Entity\Licence\Licence
     */
    #[\Override]
    protected function getEntity($entityId)
    {
        if (is_numeric($entityId)) {
            return parent::getEntity($entityId);
        }

        return $this->getRepo($this->repo)->fetchByLicNo($entityId);
    }
}

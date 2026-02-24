<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

/**
 * Can Access Licence
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CanEditLicence extends AbstractCanEditEntity
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

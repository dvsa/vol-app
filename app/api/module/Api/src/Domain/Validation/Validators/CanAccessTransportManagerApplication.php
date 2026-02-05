<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

/**
 * Can Access TransportManagerApplication
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CanAccessTransportManagerApplication extends AbstractCanAccessEntity
{
    protected $repo = 'TransportManagerApplication';

    /**
     * Can current user access a TransportManagerApplication
     *
     * @param int $entityId TransportManagerApplication ID
     *
     * @return boolean
     */
    #[\Override]
    public function isValid($entityId)
    {
        /* @var $tma \Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication */
        $tma = $this->getRepo($this->repo)->fetchById($entityId);
        if ($tma->getTransportManager() === $this->getCurrentUser()->getTransportManager()) {
            return true;
        }

        return parent::isValid($entityId);
    }
}

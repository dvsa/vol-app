<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

/**
 * Can Access PreviousConviction
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class CanAccessPreviousConviction extends AbstractCanAccessEntity
{
    protected $repo = 'PreviousConviction';

    #[\Override]
    public function isValid($entityId)
    {
        /* @var $entity \Dvsa\Olcs\Api\Entity\Application\PreviousConviction */
        $entity = $this->getRepo($this->repo)->fetchById($entityId);
        if (
            !empty($entity->getTransportManager()) &&
            $entity->getTransportManager() === $this->getCurrentUser()->getTransportManager()
        ) {
            return true;
        }

        return parent::isValid($entityId);
    }
}

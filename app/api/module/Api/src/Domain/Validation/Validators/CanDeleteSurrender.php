<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Entity\Surrender;
use Dvsa\Olcs\Api\Entity\System\RefData;

class CanDeleteSurrender extends AbstractCanAccessEntity
{
    protected $repo = 'Licence';

    /**
     * @var Surrender
     */
    private $surrender;

    #[\Override]
    public function isValid($entityId)
    {
        $this->surrender = $this->getRepo('Surrender')->fetchOneByLicenceId($entityId);

        if ($this->surrender->getStatus()->getId() === RefData::SURRENDER_STATUS_WITHDRAWN || $this->hasExpired()) {
            return parent::isValid($entityId);
        }

        return false;
    }

    private function hasExpired(): bool
    {
        $now = new \DateTimeImmutable();
        $modified = $this->getSurrenderCreatedOrModifiedOn();

        return $now->diff($modified)->days >= 2;
    }

    private function getSurrenderCreatedOrModifiedOn(): \DateTimeInterface
    {
        return $this->surrender->getLastModifiedOn(true) ?? $this->surrender->getCreatedOn(true);
    }
}

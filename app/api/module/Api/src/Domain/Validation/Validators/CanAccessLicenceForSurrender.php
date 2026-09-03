<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Domain\AuthAwareTrait;
use Dvsa\Olcs\Api\Domain\LicenceStatusAwareTrait;

class CanAccessLicenceForSurrender extends CanAccessLicence implements ValidatorInterface
{
    use LicenceStatusAwareTrait;
    use SurrenderStatusAwareTrait;
    use AuthAwareTrait;

    protected $repo = 'Licence';

    #[\Override]
    public function isValid($entityId)
    {
        $licence = $this->getRepo($this->repo)->fetchById($entityId);

        if ($this->isExternalUser()) {
            if ($licence->hasQueuedRevocation()) {
                return false;
            }

            if ($this->notBeenSurrendered($licence)) {
                return parent::isValid($entityId);
            }

            try {
                $surrender = $this->getRepo('Surrender')->fetchOneByLicenceId($entityId);
            } catch (\Dvsa\Olcs\Api\Domain\Exception\NotFoundException $e) {
                return false;
            }

            return $this->hasBeenSigned($surrender) && parent::isValid($entityId);
        }
        return parent::isValid($entityId);
    }
}

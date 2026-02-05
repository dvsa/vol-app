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
        $surrender = $this->getRepo('Surrender')->fetchOneByLicenceId($entityId);

        if ($this->isExternalUser()) {
            return $this->notBeenSurrendered($licence) || $this->hasBeenSigned($surrender)  ? parent::isValid($entityId) : false;
        }
        return parent::isValid($entityId);
    }
}

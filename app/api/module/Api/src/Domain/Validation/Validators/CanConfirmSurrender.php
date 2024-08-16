<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

/**
 * Class CanConfirmSurrender
 *
 * @package Dvsa\Olcs\Api\Domain\Validation\Validators
 */
class CanConfirmSurrender extends AbstractCanAccessEntity implements ValidatorInterface
{
    use SurrenderStatusAwareTrait;

    protected $repo = 'Licence';

    public function isValid($entityId)
    {
        $surrender = $this->getRepo('Surrender')->fetchOneByLicenceId($entityId);

        if ($this->hasBeenSigned($surrender)) {
            return parent::isValid($entityId);
        }

        return false;
    }
}

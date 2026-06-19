<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

use Dvsa\Olcs\Api\Entity\Fee\Fee;

/**
 * Can Access Fee
 */
class CanAccessFee extends AbstractCanAccessEntity
{
    protected $repo = 'Fee';

    #[\Override]
    public function isValid($entityId)
    {
        protected $repo = 'Fee';
    }
}

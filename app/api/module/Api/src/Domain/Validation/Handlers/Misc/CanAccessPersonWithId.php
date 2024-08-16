<?php

/**
 * Can Access Person With Id
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\AbstractHandler;

/**
 * Can Access Person With Id
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CanAccessPersonWithId extends AbstractHandler
{
    /**
     * @inheritdoc
     */
    public function isValid($dto)
    {
        return $this->canAccessPerson($this->getId($dto));
    }

    protected function getId($dto)
    {
        return $dto->getId();
    }
}

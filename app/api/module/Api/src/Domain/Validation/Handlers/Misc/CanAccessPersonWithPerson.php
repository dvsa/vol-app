<?php

/**
 * Can Access Person With Person
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

/**
 * Can Access Person With Person
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CanAccessPersonWithPerson extends CanAccessPersonWithId
{
    #[\Override]
    protected function getId($dto)
    {
        return $dto->getPerson();
    }
}

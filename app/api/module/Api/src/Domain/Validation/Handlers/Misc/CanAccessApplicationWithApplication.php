<?php

/**
 * Can Access Application With Application
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Validation\Handlers\Misc;

/**
 * Can Access Application With Application
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CanAccessApplicationWithApplication extends CanAccessApplicationWithId
{
    #[\Override]
    protected function getId($dto)
    {
        return $dto->getApplication();
    }
}

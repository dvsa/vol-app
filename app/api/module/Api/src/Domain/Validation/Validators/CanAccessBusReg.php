<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

/**
 * Can Access BusReg
 */
class CanAccessBusReg extends AbstractCanAccessEntity
{
    protected $repo = 'Bus';
}

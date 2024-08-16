<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

/**
 * Can Access TransportManager
 */
class CanAccessTransportManager extends AbstractCanAccessEntity
{
    protected $repo = 'TransportManager';
}

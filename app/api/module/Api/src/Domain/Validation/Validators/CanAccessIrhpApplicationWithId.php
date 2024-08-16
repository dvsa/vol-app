<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

/**
 * Can access an IRHP application
 */
class CanAccessIrhpApplicationWithId extends AbstractCanAccessEntity
{
    protected $repo = 'IrhpApplication';
}

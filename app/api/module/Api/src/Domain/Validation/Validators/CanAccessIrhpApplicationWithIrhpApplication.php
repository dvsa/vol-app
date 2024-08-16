<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

/**
 * Can access an IRHP application
 */
class CanAccessIrhpApplicationWithIrhpApplication extends AbstractCanAccessEntity
{
    protected $repo = 'IrhpApplication';
}

<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

/**
 * Can access EBSR submission
 */
class CanAccessEbsrSubmission extends AbstractCanAccessEntity
{
    protected $repo = 'EbsrSubmission';
}

<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

/**
 * Can Access TmEmployment
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CanAccessTmEmployment extends AbstractCanAccessEntity
{
    protected $repo = 'TmEmployment';
}

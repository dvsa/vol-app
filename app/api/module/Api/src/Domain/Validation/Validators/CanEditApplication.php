<?php

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

/**
 * Can Edit Application
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class CanEditApplication extends AbstractCanEditEntity
{
    protected $repo = 'Application';
}

<?php

/**
 * Can Access Organisation Person
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Validation\Validators;

/**
 * Can Access Organisation Person
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CanAccessOrganisationPerson extends AbstractCanAccessEntity
{
    protected $repo = 'OrganisationPerson';
}

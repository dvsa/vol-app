<?php

/**
 * CompanySubsidiary
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Organisation\CompanySubsidiary as Entity;

/**
 * CompanySubsidiary
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CompanySubsidiary extends AbstractRepository
{
    protected $entity = Entity::class;
}

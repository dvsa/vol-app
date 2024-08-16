<?php

/**
 * Organisation Read Audit
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Organisation\OrganisationReadAudit as Entity;

/**
 * Organisation Read Audit
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OrganisationReadAudit extends AbstractReadAudit
{
    protected $entity = Entity::class;

    protected $entityProperty = 'organisation';
}

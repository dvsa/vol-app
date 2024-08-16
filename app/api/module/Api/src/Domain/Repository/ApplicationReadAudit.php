<?php

/**
 * Application Read Audit
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Application\ApplicationReadAudit as Entity;

/**
 * Application Read Audit
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ApplicationReadAudit extends AbstractReadAudit
{
    protected $entity = Entity::class;

    protected $entityProperty = 'application';
}

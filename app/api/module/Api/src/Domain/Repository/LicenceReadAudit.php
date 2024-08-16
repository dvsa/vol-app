<?php

/**
 * Licence Read Audit
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Licence\LicenceReadAudit as Entity;

/**
 * Licence Read Audit
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceReadAudit extends AbstractReadAudit
{
    protected $entity = Entity::class;

    protected $entityProperty = 'licence';
}

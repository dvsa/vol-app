<?php

/**
 * Bus Reg Read Audit
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Bus\BusRegReadAudit as Entity;

/**
 * Bus Reg Read Audit
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class BusRegReadAudit extends AbstractReadAudit
{
    protected $entity = Entity::class;

    protected $entityProperty = 'busReg';
}

<?php

/**
 * Transport Manager Read Audit
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Tm\TransportManagerReadAudit as Entity;

/**
 * Transport Manager Read Audit
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TransportManagerReadAudit extends AbstractReadAudit
{
    protected $entity = Entity::class;

    protected $entityProperty = 'transportManager';
}

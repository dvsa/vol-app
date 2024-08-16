<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Permits\IrhpApplicationReadAudit as Entity;

/**
 * IRHP Application Read Audit
 */
class IrhpApplicationReadAudit extends AbstractReadAudit
{
    protected $entity = Entity::class;

    protected $entityProperty = 'irhpApplication';
}

<?php

/**
 * Cases Read Audit
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Cases\CasesReadAudit as Entity;

/**
 * Cases Read Audit
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CasesReadAudit extends AbstractReadAudit
{
    protected $entity = Entity::class;

    protected $entityProperty = 'case';
}

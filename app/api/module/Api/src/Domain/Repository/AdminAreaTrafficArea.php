<?php

/**
 * Admin Area Traffic Area
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\TrafficArea\AdminAreaTrafficArea as Entity;

/**
 * Admin Area Traffic Area
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class AdminAreaTrafficArea extends AbstractRepository
{
    protected $entity = Entity::class;
}

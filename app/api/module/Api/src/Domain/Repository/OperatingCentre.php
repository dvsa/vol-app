<?php

/**
 * OperatingCentre
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre as Entity;

/**
 * OperatingCentre
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class OperatingCentre extends AbstractRepository
{
    protected $entity = Entity::class;
}

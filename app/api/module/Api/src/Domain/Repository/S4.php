<?php

/**
 * S4.php
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Application\S4 as Entity;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Exception;
use Laminas\Stdlib\ArraySerializableInterface as QryCmd;

/**
 * S4 Repository.
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class S4 extends AbstractRepository
{
    protected $entity = Entity::class;
}

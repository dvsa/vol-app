<?php

/**
 * ChangeOfEntity
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Entity\Organisation\ChangeOfEntity as Entity;

/**
 * ChangeOfEntity
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ChangeOfEntity extends AbstractRepository
{
    protected $entity = Entity::class;
}

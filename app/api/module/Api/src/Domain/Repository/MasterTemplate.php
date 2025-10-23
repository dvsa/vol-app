<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Letter\MasterTemplate as Entity;

/**
 * MasterTemplate Repository
 */
class MasterTemplate extends AbstractRepository
{
    protected $entity = Entity::class;
}
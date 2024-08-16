<?php

/**
 * TransportManager
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Tm\TransportManager as Entity;

/**
 * Transport Manager Repository
 */
class TransportManager extends AbstractRepository
{
    protected $entity = Entity::class;
    protected $alias = 'tm';
}

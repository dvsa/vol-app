<?php

/**
 * Recipient
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Publication\Recipient as Entity;

/**
 * Recipient
 */
class Recipient extends AbstractRepository
{
    protected $entity = Entity::class;
}

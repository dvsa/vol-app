<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\PrintScan\Scan as Entity;

/**
 * Scan
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class Scan extends AbstractRepository
{
    protected $entity = Entity::class;
}

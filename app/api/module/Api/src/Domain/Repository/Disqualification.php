<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Organisation\Disqualification as Entity;

/**
 * Disqualification
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class Disqualification extends AbstractRepository
{
    protected $entity = Entity::class;
}

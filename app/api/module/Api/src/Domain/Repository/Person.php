<?php

/**
 * Person Repository
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Person\Person as Entity;

/**
 * Person Repository
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class Person extends AbstractRepository
{
    protected $entity = Entity::class;
    protected $alias = 'p';
}

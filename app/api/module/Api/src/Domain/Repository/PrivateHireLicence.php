<?php

/**
 * PrivateHireLicence
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Licence\PrivateHireLicence as Entity;

/**
 * PrivateHireLicence
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class PrivateHireLicence extends AbstractRepository
{
    protected $entity = Entity::class;
}

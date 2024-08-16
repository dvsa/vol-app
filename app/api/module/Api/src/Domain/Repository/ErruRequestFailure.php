<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Exception;
use Dvsa\Olcs\Api\Entity\Si\ErruRequestFailure as Entity;

/**
 * Erru Request Failure
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class ErruRequestFailure extends AbstractRepository
{
    protected $entity = Entity::class;
}

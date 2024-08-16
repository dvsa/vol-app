<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Entity\System\Language as Entity;

/**
 * Language
 */
class Language extends AbstractRepository
{
    protected $entity = Entity::class;
}

<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Letter\LetterInstance as Entity;

/**
 * LetterInstance Repository
 */
class LetterInstance extends AbstractRepository
{
    protected $entity = Entity::class;
}
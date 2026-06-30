<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Letter\LetterChoice as Entity;

/**
 * LetterChoice Repository
 */
class LetterChoice extends AbstractRepository
{
    protected $entity = Entity::class;
}

<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Letter\LetterTodoVersion as Entity;

/**
 * LetterTodoVersion Repository
 */
class LetterTodoVersion extends AbstractRepository
{
    protected $entity = Entity::class;
}

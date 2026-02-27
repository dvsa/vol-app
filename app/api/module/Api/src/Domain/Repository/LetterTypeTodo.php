<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Letter\LetterTypeTodo as Entity;

/**
 * LetterTypeTodo Repository
 */
class LetterTypeTodo extends AbstractRepository
{
    protected $entity = Entity::class;
}

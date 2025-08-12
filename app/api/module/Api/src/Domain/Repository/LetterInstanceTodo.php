<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceTodo as Entity;

/**
 * LetterInstanceTodo Repository
 */
class LetterInstanceTodo extends AbstractRepository
{
    protected $entity = Entity::class;
}
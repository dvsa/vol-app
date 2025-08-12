<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Letter\LetterType as Entity;

/**
 * LetterType Repository
 */
class LetterType extends AbstractRepository
{
    protected $entity = Entity::class;
}
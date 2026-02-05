<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Letter\LetterAppendixVersion as Entity;

/**
 * LetterAppendixVersion Repository
 */
class LetterAppendixVersion extends AbstractRepository
{
    protected $entity = Entity::class;
}

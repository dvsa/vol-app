<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Letter\LetterTestData as Entity;

/**
 * LetterTestData Repository
 */
class LetterTestData extends AbstractRepository
{
    protected $entity = Entity::class;
}

<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Letter\LetterSectionVersion as Entity;

/**
 * LetterSectionVersion Repository
 */
class LetterSectionVersion extends AbstractRepository
{
    protected $entity = Entity::class;
}
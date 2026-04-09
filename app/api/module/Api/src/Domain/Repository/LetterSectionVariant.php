<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Letter\LetterSectionVariant as Entity;

/**
 * LetterSectionVariant Repository
 */
class LetterSectionVariant extends AbstractRepository
{
    protected $entity = Entity::class;
}

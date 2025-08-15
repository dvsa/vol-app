<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Letter\LetterTypeIssue as Entity;

/**
 * LetterTypeIssue Repository
 */
class LetterTypeIssue extends AbstractRepository
{
    protected $entity = Entity::class;
}
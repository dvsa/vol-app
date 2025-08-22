<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceIssue as Entity;

/**
 * LetterInstanceIssue Repository
 */
class LetterInstanceIssue extends AbstractRepository
{
    protected $entity = Entity::class;
}
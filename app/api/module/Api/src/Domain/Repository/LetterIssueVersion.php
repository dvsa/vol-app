<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Letter\LetterIssueVersion as Entity;

/**
 * LetterIssueVersion Repository
 */
class LetterIssueVersion extends AbstractRepository
{
    protected $entity = Entity::class;
}

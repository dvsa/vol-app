<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Letter\LetterIssueType as Entity;

/**
 * LetterIssueType Repository
 */
class LetterIssueType extends AbstractRepository
{
    protected $entity = Entity::class;
}

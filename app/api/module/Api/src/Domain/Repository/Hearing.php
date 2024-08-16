<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Entity\Cases\Hearing as Entity;

/**
 * Hearing
 */
class Hearing extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * fetch a hearing by case ID
     *
     * @param int $caseId Case ID
     *
     * @return Entity
     * @throws NotFoundException
     */
    public function fetchOneByCase($caseId)
    {
        if ($caseId === null) {
            throw new NotFoundException('Case id cannot be null');
        }

        // @phpstan-ignore-next-line __call magic.
        return parent::fetchOneByCase($caseId);
    }
}

<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Letter\LetterInstance as Entity;

/**
 * LetterInstance Repository
 */
class LetterInstance extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * The letter a generated document was built from, if it came from the letter builder
     */
    public function fetchByDocument(int $documentId): ?Entity
    {
        return $this->getRepository()->findOneBy(['document' => $documentId]);
    }
}

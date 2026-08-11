<?php

/**
 * RetrievalLinkDocument
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Retrieval\RetrievalLinkDocument as Entity;

/**
 * RetrievalLinkDocument
 */
class RetrievalLinkDocument extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Resolve an opaque member reference to its bundle member (which holds the real document id,
     * server-side). Returns null when the reference is unknown.
     */
    public function fetchByMemberRef(string $memberRef): ?Entity
    {
        return $this->getEntityManager()
            ->getRepository($this->entity)
            ->findOneBy(['memberRef' => $memberRef]);
    }
}

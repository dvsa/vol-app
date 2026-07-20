<?php

/**
 * RetrievalLink
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Retrieval\RetrievalLink as Entity;

/**
 * RetrievalLink
 */
class RetrievalLink extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Look a link up by its opaque token. Returns null when the token is unknown — callers must
     * treat unknown / expired / revoked identically so the endpoint gives no existence oracle.
     */
    public function fetchByToken(string $token): ?Entity
    {
        return $this->getEntityManager()
            ->getRepository($this->entity)
            ->findOneBy(['token' => $token]);
    }

    /**
     * Bulk-delete links whose validity window has passed. The database-level ON DELETE CASCADE
     * foreign keys remove the associated member, OTP and audit-event rows. Returns the number of
     * links deleted.
     */
    public function deleteExpired(\DateTimeInterface $now): int
    {
        return (int) $this->getEntityManager()->createQueryBuilder()
            ->delete($this->entity, 'rl')
            ->where('rl.expiresAt < :now')->setParameter('now', $now)
            ->getQuery()
            ->execute();
    }
}

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
     * Bulk-delete links whose validity window has passed. The database foreign keys remove the
     * associated member and OTP rows (ON DELETE CASCADE); audit-event rows are RETAINED — their
     * link reference is nulled (ON DELETE SET NULL) so the trail survives the purge. Returns the
     * number of links deleted.
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

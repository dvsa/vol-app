<?php

/**
 * RetrievalOtp
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Retrieval\RetrievalOtp as Entity;

/**
 * RetrievalOtp
 */
class RetrievalOtp extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * The newest still-usable code for a link: not consumed, not invalidated, not expired.
     */
    public function fetchLatestActive(int $linkId, \DateTimeInterface $now): ?Entity
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select('ro')
            ->from($this->entity, 'ro')
            ->where('ro.retrievalLink = :link')->setParameter('link', $linkId)
            ->andWhere('ro.consumedAt IS NULL')
            ->andWhere('ro.invalidatedAt IS NULL')
            ->andWhere('ro.expiresAt > :now')->setParameter('now', $now)
            ->orderBy('ro.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * How many codes have been requested for a link since a cut-off — the basis of send
     * rate-limiting.
     */
    public function countRequestsSince(int $linkId, \DateTimeInterface $since): int
    {
        return (int) $this->getEntityManager()->createQueryBuilder()
            ->select('COUNT(ro.id)')
            ->from($this->entity, 'ro')
            ->where('ro.retrievalLink = :link')->setParameter('link', $linkId)
            ->andWhere('ro.createdOn >= :since')->setParameter('since', $since)
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * Retire every currently-active code for a link, so a freshly issued code is the only one
     * that can succeed.
     */
    public function invalidateActiveForLink(int $linkId, \DateTimeInterface $now): void
    {
        $this->getEntityManager()->createQueryBuilder()
            ->update($this->entity, 'ro')
            ->set('ro.invalidatedAt', ':now')->setParameter('now', $now)
            ->where('ro.retrievalLink = :link')->setParameter('link', $linkId)
            ->andWhere('ro.consumedAt IS NULL')
            ->andWhere('ro.invalidatedAt IS NULL')
            ->getQuery()
            ->execute();
    }
}

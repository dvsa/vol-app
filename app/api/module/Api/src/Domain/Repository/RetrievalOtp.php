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
     * Atomically claim one verification attempt against the cap. Returns the attempts REMAINING
     * after the claim, or -1 if no attempt could be claimed (cap reached, or already
     * consumed/invalidated). The single conditional UPDATE makes the cap race-safe: concurrent
     * verifications cannot all slip under the limit (a plain read-increment-write can).
     */
    public function claimAttempt(int $otpId): int
    {
        $em = $this->getEntityManager();

        $affected = $em->createQueryBuilder()
            ->update($this->entity, 'ro')
            ->set('ro.attempts', 'ro.attempts + 1')
            ->where('ro.id = :id')->setParameter('id', $otpId)
            ->andWhere('ro.attempts < ro.maxAttempts')
            ->andWhere('ro.consumedAt IS NULL')
            ->andWhere('ro.invalidatedAt IS NULL')
            ->getQuery()
            ->execute();

        if ($affected < 1) {
            return -1;
        }

        // Best-effort remaining for the UI (approximate under concurrency — NOT a security control;
        // the cap itself is enforced atomically above).
        $row = $em->createQueryBuilder()
            ->select('ro.attempts AS a', 'ro.maxAttempts AS m')
            ->from($this->entity, 'ro')
            ->where('ro.id = :id')->setParameter('id', $otpId)
            ->getQuery()
            ->getSingleResult();

        return max(0, (int) $row['m'] - (int) $row['a']);
    }

    /**
     * Atomically consume the code (single-use), iff still usable. Returns true only if THIS call
     * consumed it, so a concurrent double-submit cannot both succeed.
     */
    public function consume(int $otpId, \DateTimeInterface $now): bool
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->update($this->entity, 'ro')
            ->set('ro.consumedAt', ':now')->setParameter('now', $now)
            ->where('ro.id = :id')->setParameter('id', $otpId)
            ->andWhere('ro.consumedAt IS NULL')
            ->andWhere('ro.invalidatedAt IS NULL')
            ->andWhere('ro.expiresAt > :now2')->setParameter('now2', $now)
            ->getQuery()
            ->execute() > 0;
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

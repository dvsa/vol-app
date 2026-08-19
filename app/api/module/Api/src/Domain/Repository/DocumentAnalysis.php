<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;
use Dvsa\Olcs\Api\Entity\Doc\DocumentAnalysis as Entity;

/**
 * Status transitions are single atomic conditional UPDATEs: the sweeper and the result handler
 * race on the same rows, so neither may read-then-write. There are no locks.
 *
 * @method Entity fetchById($id, $hydrateMode = Query::HYDRATE_OBJECT, $version = null)
 */
class DocumentAnalysis extends AbstractRepository
{
    protected $entity = Entity::class;

    protected $alias = 'da';

    /** @param string $token raw 16 binary bytes */
    public function fetchByToken(string $token): ?Entity
    {
        $qb = $this->createQueryBuilder();

        $qb->andWhere($qb->expr()->eq($this->alias . '.token', ':token'))
            ->setParameter('token', $token)
            ->setMaxResults(1);

        return $qb->getQuery()->getOneOrNullResult(Query::HYDRATE_OBJECT);
    }

    /**
     * Documents already being analysed, or successfully analysed inside the dedupe window, so
     * that resubmitting an application cannot spawn concurrent analyses of the same document.
     *
     * @param int[] $documentIds
     *
     * @return int[] subset of $documentIds to skip
     */
    public function fetchDocumentIdsWithActiveAnalysis(array $documentIds, \DateTimeInterface $successWindowStart): array
    {
        if ($documentIds === []) {
            return [];
        }

        $qb = $this->createQueryBuilder();

        $qb->select('IDENTITY(' . $this->alias . '.document) AS documentId')
            ->andWhere($qb->expr()->in($this->alias . '.document', ':documentIds'))
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->eq($this->alias . '.status', ':pending'),
                    $qb->expr()->andX(
                        $qb->expr()->eq($this->alias . '.status', ':success'),
                        $qb->expr()->gte($this->alias . '.createdOn', ':successWindowStart')
                    )
                )
            )
            ->setParameter('documentIds', $documentIds)
            ->setParameter('pending', Entity::STATUS_PENDING)
            ->setParameter('success', Entity::STATUS_SUCCESS)
            ->setParameter('successWindowStart', $successWindowStart);

        return array_map(
            static fn(array $row): int => (int)$row['documentId'],
            $qb->getQuery()->getArrayResult()
        );
    }

    /**
     * Resolve stale PENDING rows to TIMEOUT in one atomic statement.
     *
     * @return int rows swept
     */
    public function sweepStalePending(\DateTimeInterface $threshold): int
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return (int)$qb->update(Entity::class, $this->alias)
            ->set($this->alias . '.status', ':timeout')
            ->set($this->alias . '.timedOutAt', ':now')
            ->where($qb->expr()->eq($this->alias . '.status', ':pending'))
            ->andWhere($qb->expr()->lt($this->alias . '.createdOn', ':threshold'))
            ->setParameter('timeout', Entity::STATUS_TIMEOUT)
            ->setParameter('now', new \DateTime())
            ->setParameter('pending', Entity::STATUS_PENDING)
            ->setParameter('threshold', $threshold)
            ->getQuery()
            ->execute();
    }

    /**
     * Rows sweepStalePending() would resolve, so the caller can log them. Kept separate so
     * the write stays atomic.
     *
     * @return Entity[]
     */
    public function fetchStalePending(\DateTimeInterface $threshold): array
    {
        $qb = $this->createQueryBuilder();

        $qb->andWhere($qb->expr()->eq($this->alias . '.status', ':pending'))
            ->andWhere($qb->expr()->lt($this->alias . '.createdOn', ':threshold'))
            ->setParameter('pending', Entity::STATUS_PENDING)
            ->setParameter('threshold', $threshold);

        return $qb->getQuery()->getResult();
    }

    /** @param string $token raw 16 binary bytes */
    public function createPending(
        string $token,
        ApplicationEntity $application,
        DocumentEntity $document
    ): Entity {
        $analysis = new Entity();
        $analysis->setToken($token)
            ->setApplication($application)
            ->setDocument($document)
            ->setStatus(Entity::STATUS_PENDING);

        $this->save($analysis);

        return $analysis;
    }
}

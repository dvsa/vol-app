<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;
use Dvsa\Olcs\Api\Entity\Doc\DocumentAnalysis as Entity;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Transfer\Enum\Document\AssessmentStatus;
use Dvsa\Olcs\Transfer\Query\Document\DocumentAnalysisList as DocumentAnalysisListQuery;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

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

        // Bound explicitly as BINARY: left to infer, the ORM types any string parameter as
        // STRING, which sends raw uid bytes through the connection's character set.
        $qb->andWhere($qb->expr()->eq($this->alias . '.token', ':token'))
            ->setParameter('token', $token, Types::BINARY)
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
     * Joins for fetchList() and fetchCount().
     *
     * The document is fetch-joined because callers read it on every row (for example its
     * issued date), which would otherwise be one lazy load per analysis.
     */
    #[\Override]
    protected function applyListJoins(QueryBuilder $qb)
    {
        $qb->innerJoin($this->alias . '.document', 'd')
            ->addSelect('d');
    }

    /**
     * Filters for fetchList() and fetchCount(). Paging and ordering are applied by the base
     * repository from the query's PagedTrait / OrderedTrait, so nothing here orders or limits.
     *
     * @param DocumentAnalysisListQuery $query
     */
    #[\Override]
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        // Application and variation pages: that specific application only.
        if ($query->getApplication() !== null) {
            $qb->andWhere('IDENTITY(' . $this->alias . '.application) = :applicationId')
                ->setParameter('applicationId', (int) $query->getApplication());
        }

        // Licence page: follow the analysed document's own licence link, as the documents tab
        // does, or an application (new or variation) on the licence for documents linked only
        // to the application. LEFT JOIN so rows whose application was deleted (SET NULL) can
        // still match on the document's licence.
        if ($query->getLicence() !== null) {
            $qb->leftJoin($this->alias . '.application', 'a')
                ->andWhere(
                    $qb->expr()->orX(
                        'IDENTITY(d.licence) = :licenceId',
                        'IDENTITY(a.licence) = :licenceId'
                    )
                )
                ->setParameter('licenceId', (int) $query->getLicence());
        }

        if ($query->getDocument() !== null) {
            $qb->andWhere('IDENTITY(' . $this->alias . '.document) = :documentId')
                ->setParameter('documentId', (int) $query->getDocument());
        }

        if ($query->getStatus() !== null) {
            $qb->andWhere($qb->expr()->eq($this->alias . '.status', ':status'))
                ->setParameter('status', $query->getStatus());
        }
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
     * Resolve a PENDING row to SUCCESS with its result payload, guarded by the same
     * conditional UPDATE pattern as sweepStalePending() so a row already resolved by the
     * sweeper (or a concurrent/duplicate invocation) cannot be overwritten.
     *
     * Both columns are written in the one UPDATE so a row can never hold a result without the
     * normalised form that was derived from it.
     *
     * @param array<mixed> $result the analysis report exactly as the pipeline produced it
     * @param array<mixed>|null $resultNormalised the report mapped to the assessment payload, or
     *                                            null when it held nothing that could be mapped
     *
     * @return int rows affected (0 if the row was no longer PENDING)
     */
    public function recordSuccess(int $analysisId, array $result, ?array $resultNormalised): int
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return (int)$qb->update(Entity::class, $this->alias)
            ->set($this->alias . '.status', ':success')
            ->set($this->alias . '.result', ':result')
            ->set($this->alias . '.resultNormalised', ':resultNormalised')
            ->set($this->alias . '.completedAt', ':now')
            ->where($qb->expr()->eq($this->alias . '.id', ':id'))
            ->andWhere($qb->expr()->eq($this->alias . '.status', ':pending'))
            ->setParameter('success', Entity::STATUS_SUCCESS)
            ->setParameter('result', $result, Types::JSON)
            ->setParameter('resultNormalised', $resultNormalised, Types::JSON)
            ->setParameter('now', new \DateTime())
            ->setParameter('id', $analysisId)
            ->setParameter('pending', Entity::STATUS_PENDING)
            ->getQuery()
            ->execute();
    }

    /**
     * Resolve a PENDING row to ERROR with its failure detail, guarded by the same
     * conditional UPDATE pattern as recordSuccess().
     *
     * @return int rows affected (0 if the row was no longer PENDING)
     */
    public function recordError(int $analysisId, string $errorDetail): int
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return (int)$qb->update(Entity::class, $this->alias)
            ->set($this->alias . '.status', ':error')
            ->set($this->alias . '.errorDetail', ':errorDetail')
            ->set($this->alias . '.completedAt', ':now')
            ->where($qb->expr()->eq($this->alias . '.id', ':id'))
            ->andWhere($qb->expr()->eq($this->alias . '.status', ':pending'))
            ->setParameter('error', Entity::STATUS_ERROR)
            ->setParameter('errorDetail', $errorDetail)
            ->setParameter('now', new \DateTime())
            ->setParameter('id', $analysisId)
            ->setParameter('pending', Entity::STATUS_PENDING)
            ->getQuery()
            ->execute();
    }

    /**
     * Record a caseworker's review of a successful analysis, as one conditional UPDATE like the
     * status transitions above. Only SUCCESS rows can be reviewed; anything else (still pending,
     * failed, timed out, or no such row) matches nothing.
     *
     * Used for a caseworker's deliberate change of a decision (UpdateDocumentAnalysisAssessmentStatus);
     * it may overwrite an existing assessment status. A first decision
     * (AcceptDocumentAnalysisReview) uses recordInitialAssessmentStatus() instead, which cannot.
     *
     * @return int rows affected (0 if no successful analysis has that id)
     */
    public function recordAssessmentStatus(int $analysisId, AssessmentStatus $status, UserEntity $reviewedBy): int
    {
        return (int)$this->assessmentStatusUpdate($analysisId, $status, $reviewedBy)
            ->getQuery()
            ->execute();
    }

    /**
     * Record a caseworker's first review of a successful analysis. The same guarded UPDATE as
     * recordAssessmentStatus(), with one extra condition: the row must not already hold a
     * decision (APPROVED or REJECTED), so a row decided concurrently (by this analysis having
     * been accepted elsewhere) cannot be overwritten. NULL and PENDING both count as undecided:
     * PENDING is a decision explicitly reverted by UpdateDocumentAnalysisAssessmentStatus, so an
     * analysis reset that way is reviewable again here exactly as an unreviewed one is.
     *
     * A deliberate change of a decision already made goes through recordAssessmentStatus() via
     * UpdateDocumentAnalysisAssessmentStatus instead.
     *
     * @return int rows affected (0 if no successful, undecided analysis has that id)
     */
    public function recordInitialAssessmentStatus(int $analysisId, AssessmentStatus $status, UserEntity $reviewedBy): int
    {
        $qb = $this->assessmentStatusUpdate($analysisId, $status, $reviewedBy);

        return (int)$qb
            ->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->isNull($this->alias . '.assessmentStatus'),
                    $qb->expr()->notIn($this->alias . '.assessmentStatus', ':decided')
                )
            )
            ->setParameter('decided', [AssessmentStatus::APPROVED->value, AssessmentStatus::REJECTED->value])
            ->getQuery()
            ->execute();
    }

    /**
     * The UPDATE common to both assessment status writes: the SET clause, the base SUCCESS
     * guard, and the audit columns, which are set explicitly here because a bulk UPDATE bypasses
     * the ORM's lifecycle and Blameable listeners, as User::updateLastLogin() does.
     */
    private function assessmentStatusUpdate(int $analysisId, AssessmentStatus $status, UserEntity $reviewedBy): QueryBuilder
    {
        $qb = $this->getEntityManager()->createQueryBuilder();

        return $qb->update(Entity::class, $this->alias)
            ->set($this->alias . '.assessmentStatus', ':assessmentStatus')
            ->set($this->alias . '.lastModifiedOn', ':now')
            ->set($this->alias . '.lastModifiedBy', ':lastModifiedBy')
            ->where($qb->expr()->eq($this->alias . '.id', ':id'))
            ->andWhere($qb->expr()->eq($this->alias . '.status', ':success'))
            ->setParameter('assessmentStatus', $status->value)
            ->setParameter('now', new \DateTime())
            ->setParameter('lastModifiedBy', $reviewedBy->getId())
            ->setParameter('id', $analysisId)
            ->setParameter('success', Entity::STATUS_SUCCESS);
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

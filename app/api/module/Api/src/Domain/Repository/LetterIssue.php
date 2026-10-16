<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Letter\LetterIssue as Entity;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssueVersion;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Doctrine\ORM\Query;

/**
 * LetterIssue Repository
 */
class LetterIssue extends AbstractVersionedRepository
{
    protected $entity = Entity::class;

    /**
     * Fetch list of letter issues with current version
     *
     * @param QueryInterface $query
     * @param int $hydrateMode
     * @return array
     */
    #[\Override]
    public function fetchList(QueryInterface $query, $hydrateMode = Query::HYDRATE_OBJECT)
    {
        $qb = $this->createQueryBuilder();

        $this->buildDefaultListQuery($qb, $query);

        // Join currentVersion so bundle can load nested relationships (letterIssueType, etc)
        $qb->leftJoin($this->alias . '.currentVersion', 'cv');

        $this->applyListFilters($qb, $query);

        return $this->fetchPaginatedList($qb, $hydrateMode);
    }

    /**
     * Fetch count of letter issues
     *
     * @param QueryInterface $query
     * @return int
     */
    #[\Override]
    public function fetchCount(QueryInterface $query)
    {
        $qb = $this->createQueryBuilder();
        $this->buildDefaultListQuery($qb, $query);
        $this->applyListFilters($qb, $query);
        $qb->resetDQLPart('orderBy');

        return $this->fetchPaginatedCount($qb);
    }

    /**
     * Names of the letter types using any version of the issue
     *
     * @param int $id
     * @return string[]
     */
    public function fetchLetterTypeNamesUsing(int $id): array
    {
        return $this->getEntityManager()->getConnection()->fetchFirstColumn(
            'SELECT DISTINCT lt.name FROM letter_type lt ' .
            'JOIN letter_type_issue lti ON lti.letter_type_id = lt.id ' .
            'JOIN letter_issue_version v ON v.id = lti.letter_issue_version_id ' .
            'WHERE v.letter_issue_id = :id ' .
            'ORDER BY lt.name',
            ['id' => $id]
        );
    }

    /**
     * Whether any generated letter uses a version of the issue
     *
     * @param int $id
     * @return bool
     */
    public function isUsedByLetterInstances(int $id): bool
    {
        return $this->getEntityManager()->getConnection()->fetchOne(
            'SELECT 1 FROM letter_instance_issue lii ' .
            'JOIN letter_issue_version v ON v.id = lii.letter_issue_version_id ' .
            'WHERE v.letter_issue_id = :id LIMIT 1',
            ['id' => $id]
        ) !== false;
    }

    /**
     * Delete the issue, all its versions and their to-do links
     *
     * @param int $id
     * @return void
     */
    public function hardDelete(int $id): void
    {
        $this->deleteRows($id, [
            'UPDATE letter_issue SET current_version_id = NULL WHERE id = :id',
            'DELETE lit FROM letter_issue_todo lit ' .
            'JOIN letter_issue_version v ON v.id = lit.letter_issue_version_id ' .
            'WHERE v.letter_issue_id = :id',
            'DELETE FROM letter_issue_version WHERE letter_issue_id = :id',
            'DELETE FROM letter_issue WHERE id = :id',
        ]);
    }

    /**
     * Get the list of fields that should trigger versioning when changed
     *
     * @return array
     */
    #[\Override]
    protected function getVersionedFields(): array
    {
        return [
            'category',
            'subCategory',
            'heading',
            'modalLabel',
            'defaultBodyContent',
            'helpText',
            'minLength',
            'maxLength',
            'requiresInput',
            'isNi',
            'goodsOrPsv',
            'letterIssueType'
        ];
    }

    /**
     * Get the version entity class name
     *
     * @return string
     */
    #[\Override]
    protected function getVersionEntityClass(): string
    {
        return LetterIssueVersion::class;
    }

    /**
     * Get the parent entity short name (for setter method)
     *
     * @return string
     */
    #[\Override]
    protected function getEntityShortName(): string
    {
        return 'LetterIssue';
    }
}

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

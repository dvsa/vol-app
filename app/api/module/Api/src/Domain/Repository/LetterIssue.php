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

        // Join currentVersion so bundle can load nested relationships (letterIssueType, etc)
        $qb->leftJoin($this->alias . '.currentVersion', 'cv');

        // Apply standard list filters (paging, sorting, etc)
        $this->applyListFilters($qb, $query);

        return $qb->getQuery()->getResult($hydrateMode);
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
        $this->applyListFilters($qb, $query);

        return $this->fetchPaginatedCount($qb);
    }

    /**
     * Get the list of fields that should trigger versioning when changed
     *
     * @return array
     */
    protected function getVersionedFields(): array
    {
        return [
            'category',
            'subCategory',
            'heading',
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
    protected function getVersionEntityClass(): string
    {
        return LetterIssueVersion::class;
    }

    /**
     * Get the parent entity short name (for setter method)
     *
     * @return string
     */
    protected function getEntityShortName(): string
    {
        return 'LetterIssue';
    }
}

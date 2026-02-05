<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Entity\Task\TaskAllocationRule as Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

class TaskAllocationRule extends AbstractRepository
{
    protected $entity = Entity::class;

    public function fetchByParameters(
        int $categoryId,
        ?int $subCategoryId = null,
        ?string $operatorType = null,
        ?string $ta = null,
        ?bool $isMlh = null
    ): array {
        $qb = $this->createQueryBuilder();

        $qb
            ->andWhere($qb->expr()->eq('m.category', ':category'))
            ->setParameter('category', $categoryId);

        if ($subCategoryId) {
            $qb
                ->andWhere($qb->expr()->eq('m.subCategory', ':subCategory'))
                ->setParameter('subCategory', $subCategoryId);
        } else {
            $qb
                ->andWhere($qb->expr()->isNull('m.subCategory'));
        }

        if ($operatorType) {
            $qb
                ->andWhere($qb->expr()->eq('m.goodsOrPsv', ':operatorType'))
                ->setParameter('operatorType', $operatorType);
        } else {
            $qb
                ->andWhere($qb->expr()->isNull('m.goodsOrPsv'));
        }

        if ($ta) {
            $qb
                ->andWhere($qb->expr()->eq('m.trafficArea', ':trafficArea'))
                ->setParameter('trafficArea', $ta);
        } else {
            $qb
                ->andWhere($qb->expr()->isNull('m.trafficArea'));
        }

        if ($isMlh !== null) {
            $qb
                ->andWhere($qb->expr()->eq('m.isMlh', ':isMlh'))
                ->setParameter('isMlh', $isMlh);
        } else {
            $qb
                ->andWhere($qb->expr()->isNull('m.isMlh'));
        }

        return $qb->getQuery()->getResult(Query::HYDRATE_OBJECT);
    }

    /**
     * Returns matching task allocation rules (as per $this->fetchByParameters); attempting to find a matching
     * task allocation rule using provided arguments (including sub-category). Should a rule not be found when
     * using sub-category, attempts to find a matching rule without sub-category.
     */
    public function fetchByParametersWithFallbackWhenSubCategoryNotFound(
        int $categoryId,
        int $subCategoryId,
        ?string $operatorType = null,
        ?string $ta = null,
        ?bool $isMlh = null
    ): array {
        $result = $this->fetchByParameters($categoryId, $subCategoryId, $operatorType, $ta, $isMlh);
        if ($subCategoryId !== null && count($result) === 0) {
            return $this->fetchByParameters($categoryId, null, $operatorType, $ta, $isMlh);
        }

        return $result;
    }

    #[\Override]
    public function buildDefaultListQuery(
        QueryBuilder $qb,
        QueryInterface $query,
        $compositeFields = []
    ): void {
        // add calculated columns to allow ordering by them
        parent::buildDefaultListQuery($qb, $query, ['categoryDescription', 'criteria', 'trafficAreaName']);

        $queryBuilderHelper = $this->getQueryBuilder();
        $queryBuilderHelper->with('category', 'cat');
        $queryBuilderHelper->with('goodsOrPsv', 'gop');
        $queryBuilderHelper->with('trafficArea', 'ta');
        $qb->addSelect('cat.description as HIDDEN categoryDescription');
        $qb->addSelect('gop.id as HIDDEN criteria');
        $qb->addSelect('ta.name as HIDDEN trafficAreaName');
    }
}

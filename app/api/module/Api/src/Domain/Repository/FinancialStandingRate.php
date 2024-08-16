<?php

/**
 * Financial Standing Rate
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Entity\System\FinancialStandingRate as Entity;

/**
 * Financial Standing Rate
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class FinancialStandingRate extends AbstractRepository
{
    protected $entity = Entity::class;

    protected $alias = 'fsr';

    /**
     *
     * @return array \Dvsa\Olcs\Api\Entity\System\FinancialStandingRate
     * @throws NotFoundException
     */
    public function fetchRatesInEffect(\DateTime $date)
    {
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()->withRefdata();

        $qb
            ->andWhere($qb->expr()->isNull($this->alias . '.deletedDate'))
            ->andWhere($qb->expr()->lte($this->alias . '.effectiveFrom', ':effectiveFrom'))
            ->addOrderBy($this->alias . '.effectiveFrom', 'DESC')
            ->setParameter('effectiveFrom', $date);

        $results = $qb->getQuery()->execute();

        if (empty($results)) {
            throw new NotFoundException('No effective rates found');
        }

        return $results;
    }

    /**
     * @param string $goodsOrPsv
     * @param string $licenceType
     * @param string $vehicleType
     * @param string $date
     *
     * @return Entity
     */
    public function fetchByCategoryTypeAndDate($goodsOrPsv, $licenceType, $vehicleType, $date)
    {
        $qb = $this->createQueryBuilder();

        $qb->andWhere($qb->expr()->eq($this->alias . '.goodsOrPsv', ':goodsOrPsv'));
        $qb->andWhere($qb->expr()->eq($this->alias . '.licenceType', ':licenceType'));
        $qb->andWhere($qb->expr()->eq($this->alias . '.vehicleType', ':vehicleType'));
        $qb->andWhere($qb->expr()->eq($this->alias . '.effectiveFrom', ':date'));

        $qb->setParameter('goodsOrPsv', $goodsOrPsv);
        $qb->setParameter('licenceType', $licenceType);
        $qb->setParameter('vehicleType', $vehicleType);
        $qb->setParameter('date', $date);

        return $qb->getQuery()->getResult();
    }
}

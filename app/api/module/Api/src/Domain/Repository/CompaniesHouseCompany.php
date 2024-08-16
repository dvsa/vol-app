<?php

/**
 * CompaniesHouseCompany
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Exception;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Entity\CompaniesHouse\CompaniesHouseCompany as Entity;

/**
 * CompaniesHouseCompany
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class CompaniesHouseCompany extends AbstractRepository
{
    protected $entity = Entity::class;

    protected $alias = 'cc';

    /**
     * @param $number
     *
     * @return mixed
     * @throws NotFoundException
     */
    public function getLatestByCompanyNumber($number)
    {
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()->modifyQuery($qb)
            ->withRefdata()
            ->order('createdOn', 'DESC');
        $qb
            ->andWhere($qb->expr()->eq($this->alias . '.companyNumber', ':companyNumber'))
            ->setParameter('companyNumber', $number)
            ->setMaxResults(1);

        $results = $qb->getQuery()->getResult();

        if (empty($results)) {
            throw new NotFoundException('Company with number ' . $number . ' not found');
        }

        return $results[0];
    }
}

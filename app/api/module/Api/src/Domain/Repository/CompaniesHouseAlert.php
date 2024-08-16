<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Entity\CompaniesHouse\CompaniesHouseAlert as CompaniesHouseAlertEntity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * CompaniesHouseAlert
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class CompaniesHouseAlert extends AbstractRepository
{
    protected $entity = CompaniesHouseAlertEntity::class;

    protected $alias = 'cha';

    protected function applyListJoins(QueryBuilder $qb)
    {
        parent::applyListJoins($qb);
        $qb
            ->addSelect('cha_o', 'cha_o_ls', 'cha_o_lst')
            ->innerJoin('cha.organisation', 'cha_o')
            ->innerJoin('cha_o.licences', 'cha_o_ls', Join::WITH, 'cha_o_ls.status IN (:licenceStatuses)')
            ->innerJoin('cha_o_ls.licenceType', 'cha_o_lst')
            ->setParameter(
                'licenceStatuses',
                [
                    Entity\Licence\Licence::LICENCE_STATUS_CURTAILED,
                    Entity\Licence\Licence::LICENCE_STATUS_VALID,
                    Entity\Licence\Licence::LICENCE_STATUS_SUSPENDED
                ]
            );
    }


    /**
     * Apply List Filters
     *
     * @param QueryBuilder                                       $qb    Doctrine Query
     * @param \Dvsa\Olcs\Transfer\Query\CompaniesHouse\AlertList $query Http Query
     *
     * @return void
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        if (!$query->getIncludeClosed()) {
            $qb->andWhere($qb->expr()->eq('cha.isClosed', 0));
        }

        if ($query->getTypeOfChange()) {
            $qb
                ->innerJoin(
                    $this->alias . '.reasons',
                    'r',
                    Join::WITH,
                    $qb->expr()->eq('r.reasonType', ':reasonType')
                )
                ->setParameter('reasonType', $query->getTypeOfChange());
        }

        if (method_exists($query, 'getTrafficAreas')) {
            $qb->andWhere($qb->expr()->in('cha_o_ls.trafficArea', ':trafficAreas'));
            $qb->setParameter('trafficAreas', $query->getTrafficAreas());
        }
    }

    /**
     * Get Reason Value Options
     *
     * @return array
     */
    public function getReasonValueOptions()
    {
        /** @var \Doctrine\ORM\EntityRepository $repo */
        $repo = $this->getEntityManager()->getRepository(Entity\System\RefData::class);

        $qb = $repo->createQueryBuilder('r');
        $qb
            ->where($qb->expr()->eq('r.refDataCategoryId', ':CATEGORY_ID'))
            ->setParameter('CATEGORY_ID', 'ch_alert_reason');

        $results = $qb->getQuery()->getArrayResult();

        $valueOptions = [];
        foreach ($results as $result) {
            $valueOptions[$result['id']] = $result['description'];
        }

        return $valueOptions;
    }
}

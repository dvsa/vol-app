<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Exception;
use Dvsa\Olcs\Transfer\Query as TransferQry;
use Laminas\Stdlib\ArraySerializableInterface as QryCmd;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Cases
 */
class Cases extends AbstractRepository
{
    protected $entity = Entity\Cases\Cases::class;

    private static $aliasLic = 'l';
    private static $aliasApp = 'a';
    private static $aliasTa = 'ta';

    /**
     * Fetch With Licence Using Id
     *
     * @param QryCmd $query       Http Query
     * @param int    $hydrateMode Hydrate mode
     * @param int|null   $version     Version
     *
     * @return mixed
     * @throws Exception\NotFoundException
     */
    public function fetchWithLicenceUsingId(QryCmd $query, $hydrateMode = Query::HYDRATE_OBJECT, $version = null)
    {
        $qb = $this->createQueryBuilder();

        parent::buildDefaultQuery($qb, $query->getId());

        $this->getQueryBuilder()
            ->with('licence', 'l')
            ->with('l.operatingCentres', 'loc')
            ->with('loc.operatingCentre', 'oc')
            ->with('oc.address');

        $results = $qb->getQuery()->getResult($hydrateMode);

        if (empty($results)) {
            throw new Exception\NotFoundException('Resource not found');
        }

        if ($hydrateMode === AbstractQuery::HYDRATE_OBJECT && $version !== null) {
            $this->lock($results[0], $version);
        }

        return $results[0];
    }

    /**
     * Applies filters
     *
     * @param QueryBuilder   $qb    Doctrine Query Builder
     * @param QueryInterface $query Http Query
     *
     * @return void
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        $expr = $qb->expr();

        if (method_exists($query, 'getTransportManager')) {
            $qb->andWhere($qb->expr()->eq($this->alias . '.transportManager', ':byTransportManager'))
                ->setParameter('byTransportManager', $query->getTransportManager());
        }

        if (method_exists($query, 'getLicence')) {
            $qb->andWhere($qb->expr()->eq($this->alias . '.licence', ':byLicence'))
                ->setParameter('byLicence', $query->getLicence());
        }

        if (method_exists($query, 'getCaseType') && !empty($query->getCaseType())) {
            $qb->andWhere($expr->eq($this->alias . '.caseType', ':CASE_TYPE'))
                ->setParameter('CASE_TYPE', $query->getCaseType());
        }

        if ($query instanceof TransferQry\Cases\Report\OpenList) {
            $qb->andWhere(
                $expr->isNull($this->alias . '.closedDate')
            );

            if (!empty($query->getApplicationStatus())) {
                $qb->andWhere($expr->eq(self::$aliasApp . '.status', ':APP_STATUS'))
                    ->setParameter('APP_STATUS', $query->getApplicationStatus());
            }

            if (!empty($query->getLicenceStatus())) {
                $qb->andWhere($expr->eq(self::$aliasLic . '.status', ':LIC_STATUS'))
                    ->setParameter('LIC_STATUS', $query->getLicenceStatus());
            }

            $trafficAreas = $query->getTrafficAreas();

            if (($key = array_search('other', $trafficAreas)) !== false) {
                //remove 'other' from the list
                unset($trafficAreas[$key]);

                $qb->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->isNull(self::$aliasTa . '.id'),
                        $qb->expr()->in(self::$aliasTa . '.id', ':trafficAreas')
                    )
                );
            } else {
                $qb->andWhere($qb->expr()->in(self::$aliasTa . '.id', ':trafficAreas'));
            }

            $qb->setParameter('trafficAreas', $trafficAreas);
        }
    }

    /**
     * Add joins to list query
     *
     * @param QueryBuilder $qb Doctrine Query Builder
     *
     * @return void
     */
    protected function applyListJoins(QueryBuilder $qb)
    {
        if ($this->query instanceof TransferQry\Cases\Report\OpenList) {
            $this->getQueryBuilder()
                ->with('licence', self::$aliasLic)
                ->with('application', self::$aliasApp)
                ->with(self::$aliasLic . '.trafficArea', self::$aliasTa);
        }

        parent::applyListJoins($qb);
    }

    /**
     * Fetch Extended
     *
     * @param int $caseId Case Id
     *
     * @return Entity\Cases\Cases
     * @throws Exception\NotFoundException
     */
    public function fetchExtended($caseId)
    {
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()->modifyQuery($qb)
            ->with('licence', 'l')
            ->with('application', 'a')
            ->with('transportManager', 'tm')
            ->byId($caseId);

        $res = $qb->getQuery()->getResult();
        if (!$res) {
            throw new Exception\NotFoundException('Resource not found');
        }
        return $res[0];
    }

    /**
     * Define composite columns as HIDDEN to enable ordering by case type
     *
     * @param QueryBuilder   $qb              Doctrine Query Builder
     * @param QueryInterface $query           Http Query
     * @param array          $compositeFields Composite fields
     *
     * @return void
     */
    #[\Override]
    public function buildDefaultListQuery(
        \Doctrine\ORM\QueryBuilder $qb,
        \Dvsa\Olcs\Transfer\Query\QueryInterface $query,
        $compositeFields = []
    ) {
        // add calculated columns to allow ordering by them
        parent::buildDefaultListQuery($qb, $query, ['caseType']);

        $queryBuilderHelper = $this->getQueryBuilder();
        $queryBuilderHelper->with('caseType', 'ct');
        $qb->addSelect('CONCAT(ct.description, ' . $this->alias . '.id) as HIDDEN caseType');
    }

    /**
     * @param int            $hydrateMode
     * @param null           $version
     *
     * @return mixed
     * @throws Exception\NotFoundException
     * @throws Exception\RuntimeException
     * @throws Exception\VersionConflictException
     */
    public function fetchOpenCasesForSurrender(
        QueryInterface $query,
        $hydrateMode = Query::HYDRATE_OBJECT,
        $version = null
    ) {

        $qb = $this->createQueryBuilder();
        $this->buildDefaultListQuery(
            $qb,
            $query
        );
        $expr = $qb->expr();

        $qb->andWhere($expr->eq($this->alias . '.licence', ':byLicence'))
            ->setParameter('byLicence', $query->getId());

        $qb->andWhere(
            $expr->isNull($this->alias . '.closedDate')
        );

        $results = $qb->getQuery()->getResult($hydrateMode);
        return $results;
    }

    /**
     * @return Entity\Cases\Cases[]
     *
     * @throws Exception\NotFoundException
     */
    public function fetchOpenCasesForApplication(int $id): array
    {
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()->modifyQuery($qb)
            ->with('application', 'a');
        $expr = $qb->expr();

        $qb->Where($expr->eq(self::$aliasApp . '.id', ':byApplication'))
            ->setParameter('byApplication', $id);
        $qb->andWhere(
            $expr->isNull($this->alias . '.closedDate')
        );

        return $qb->getQuery()->getResult();
    }
}

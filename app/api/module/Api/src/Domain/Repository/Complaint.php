<?php

/**
 * Complaint
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Exception;
use Laminas\Stdlib\ArraySerializableInterface as QryCmd;
use Dvsa\Olcs\Api\Entity\Cases\Complaint as Entity;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * Complaint
 */
class Complaint extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Fetch the default record by its id
     *
     * @param Query|QryCmd $query
     * @param int $hydrateMode
     * @param int|null $version
     * @return mixed
     * @throws Exception\NotFoundException
     * @throws Exception\VersionConflictException
     */
    #[\Override]
    public function fetchUsingId(QryCmd $query, $hydrateMode = Query::HYDRATE_OBJECT, $version = null)
    {
        $qb = $this->createQueryBuilder();

        $this->buildDefaultQuery($qb, $query->getId());

        $this->applyFetchJoins($qb);

        $qb->andWhere($qb->expr()->eq($this->alias . '.isCompliance', ':byIsCompliance'))
            ->setParameter('byIsCompliance', $query->getIsCompliance());

        $results = $qb->getQuery()->getResult($hydrateMode);

        if (empty($results)) {
            throw new Exception\NotFoundException('Resource not found');
        }

        if ($hydrateMode === Query::HYDRATE_OBJECT && $version !== null) {
            $this->lock($results[0], $version);
        }

        return $results[0];
    }

    /**
     * Overridden default query to return appropriate table joins
     * @param QueryBuilder $qb
     * @param int $id
     * @return \Dvsa\Olcs\Api\Domain\QueryBuilder
     */
    #[\Override]
    protected function buildDefaultQuery(QueryBuilder $qb, $id)
    {
        return $this->getQueryBuilder()->modifyQuery($qb)
            ->withRefdata()
            ->with('complainantContactDetails', 'cd')
            ->with('cd.person')
            ->with('operatingCentres', 'oc')
            ->with('oc.address')
            ->byId($id);
    }

    /**
     * Override to add additional data to the default fetchList() method
     * @param QueryBuilder $qb
     */
    protected function applyListJoins(QueryBuilder $qb)
    {
        $this->getQueryBuilder()
            ->with('complainantContactDetails', 'ccd')
            ->with('ccd.person')
            ->with('operatingCentres', 'oc')
            ->with('oc.address');
    }

    /**
     * @param QueryBuilder $qb
     * @param QueryInterface $query
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        if ($query->getCase()) {
            $qb->andWhere($qb->expr()->eq($this->alias . '.case', ':byCase'))
                ->setParameter('byCase', $query->getCase());
        }
        if ($query->getIsCompliance() !== null) {
            $qb->andWhere($qb->expr()->eq($this->alias . '.isCompliance', ':isCompliance'))
                ->setParameter('isCompliance', $query->getIsCompliance());
        }
        if ($query->getLicence() !== null) {
            $this->getQueryBuilder()->with('case', 'ca');
            $qb->andWhere($qb->expr()->eq('ca.licence', ':licence'))
                ->setParameter('licence', $query->getLicence());
        }
        if ($query->getApplication() !== null) {
            $this->getQueryBuilder()->with('case', 'ca');
            $qb->andWhere($qb->expr()->eq('ca.application', ':application'))
                ->setParameter('application', $query->getApplication());
        }
    }
}

<?php

/**
 * Workshop
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity\Licence\Workshop as Entity;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;

/**
 * Workshop
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Workshop extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Build the default query
     *
     * @param QueryBuilder $qb Doctrine query builder
     * @param int          $id Identifier
     *
     * @return \Dvsa\Olcs\Api\Domain\QueryBuilder
     */
    #[\Override]
    protected function buildDefaultQuery(QueryBuilder $qb, $id)
    {
        return parent::buildDefaultQuery($qb, $id)->withContactDetails();
    }

    /**
     * Apply filters to default query
     *
     * @param QueryBuilder   $qb    Doctrine QueryBuilder
     * @param QueryInterface $query DTO
     *
     * @return void
     */
    public function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        if ($query instanceof \Dvsa\Olcs\Transfer\Query\Licence\Safety) {
            $qb->andWhere($qb->expr()->eq($this->alias . '.licence', ':byLicence'))
                ->setParameter('byLicence', $query->getId());
        }
        if ($query instanceof \Dvsa\Olcs\Transfer\Query\Application\Safety) {
            $application = $this->getReference(ApplicationEntity::class, $query->getId());
            $qb->andWhere($qb->expr()->eq($this->alias . '.licence', ':byLicence'))
                ->setParameter('byLicence', $application->getLicence()->getId());
        }
    }

    /**
     * Fetch Workshops for a licence (with contact details and address)
     *
     * @param int $licenceId   Licence ID
     * @param int $hydrateMode Hydration mode Query::HYDRATE_* constant
     *
     * @return array
     */
    public function fetchForLicence($licenceId, $hydrateMode = Query::HYDRATE_OBJECT)
    {
        $qb = $this->createQueryBuilder();
        $this->getQueryBuilder()->modifyQuery($qb)
            ->withRefdata()
            ->with('contactDetails', 'cd')
            ->with('cd.address');

        $qb->where($qb->expr()->eq($this->alias . '.licence', ':licenceId'));
        $qb->setParameter('licenceId', $licenceId);

        return $qb->getQuery()->getResult($hydrateMode);
    }
}

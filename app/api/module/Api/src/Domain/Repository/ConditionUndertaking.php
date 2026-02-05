<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking as Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * ConditionUndertaking
 */
class ConditionUndertaking extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Fetch the default record by it's id
     *
     * Overridden default query to return appropriate table joins
     *
     * @param QueryBuilder $qb Doctrine Query Builder
     * @param int          $id CU id
     *
     * @return \Dvsa\Olcs\Api\Domain\QueryBuilder
     */
    #[\Override]
    protected function buildDefaultQuery(QueryBuilder $qb, $id)
    {
        return $this->getQueryBuilder()->modifyQuery($qb)
            ->withRefdata()
            ->with('operatingCentre', 'oc')
            ->with('oc.address')
            ->byId($id);
    }

    /**
     * Fetch a list for a licence, filtered to include only not fulfilled and not draft
     *
     * @param int $licenceId Licence Id
     *
     * @return array of Entity
     */
    public function fetchListForLicenceReadOnly($licenceId)
    {
        /* @var \Doctrine\Orm\QueryBuilder $qb*/
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()->modifyQuery($qb)
            ->withRefdata()
            ->with('attachedTo')
            ->with('conditionType')
            ->with('operatingCentre', 'oc')
            ->with('oc.address');

        $qb->andWhere($qb->expr()->eq($this->alias . '.licence', ':licence'))
            ->setParameter('licence', $licenceId);
        $qb->andWhere($qb->expr()->eq($this->alias . '.isDraft', '0'));
        $qb->andWhere($qb->expr()->eq($this->alias . '.isFulfilled', '0'));

        return $qb->getQuery()->getResult();
    }

    /**
     * Apply List Filters
     *
     * @param QueryBuilder   $qb    Doctrine Query Builder
     * @param QueryInterface $query Http Query
     *
     * @return void
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        $qb->andWhere($qb->expr()->eq($this->alias . '.case', ':byCase'))
            ->setParameter('byCase', $query->getCase());
    }

    /**
     * Add List Joins
     *
     * @param QueryBuilder $qb Doctrine Query Builder
     *
     * @return void
     */
    protected function applyListJoins(QueryBuilder $qb)
    {
        $this->getQueryBuilder()->modifyQuery($qb)
            ->withRefdata()
            ->with('operatingCentre', 'oc')
            ->with('oc.address')
            ->with('createdBy')
            ->with('lastModifiedBy');
    }

    /**
     * Fetch a list of ConditionUndertaking for an Application
     *
     * @param int $applicationId Application Id
     *
     * @return array
     */
    public function fetchListForApplication($applicationId)
    {
        /* @var \Doctrine\Orm\QueryBuilder $qb*/
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()
            ->modifyQuery($qb)
            ->with('attachedTo')
            ->with('conditionType')
            ->with('operatingCentre', 'oc')
            ->with('oc.address', 'add')
            ->with('add.countryCode')
            ->with('addedVia');

        $qb->andWhere($qb->expr()->eq($this->alias . '.application', ':application'))
            ->setParameter('application', $applicationId);

        return $qb->getQuery()->getResult();
    }

    /**
     * Fetch a list of ConditionUndertaking for a Variation
     *
     * @param int $applicationId Application Id
     * @param int $licenceId     Licence Id
     *
     * @return array
     */
    public function fetchListForVariation($applicationId, $licenceId)
    {
        /* @var \Doctrine\Orm\QueryBuilder $qb*/
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()
            ->modifyQuery($qb)
            ->with('attachedTo')
            ->with('conditionType')
            ->with('operatingCentre', 'oc')
            ->with('oc.address', 'add')
            ->with('add.countryCode')
            ->with('licConditionVariation')
            ->with('addedVia')
            ->order('id', 'ASC');

        $qb->andWhere($qb->expr()->eq($this->alias . '.application', ':application'))
            ->setParameter('application', $applicationId);
        $qb->orWhere($qb->expr()->eq($this->alias . '.licence', ':licence'))
            ->setParameter('licence', $licenceId);

        return $qb->getQuery()->getResult();
    }

    /**
     * Fetch a list of ConditionUndertaking for a Licence
     *
     * @param int    $licenceId     Licence Id
     * @param string $conditionType Condition Type
     *
     * @return array
     */
    public function fetchListForLicence($licenceId, $conditionType = null)
    {
        /* @var \Doctrine\Orm\QueryBuilder $qb*/
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()
            ->modifyQuery($qb)
            ->with('attachedTo')
            ->with('conditionType')
            ->with('operatingCentre', 'oc')
            ->with('oc.address', 'add')
            ->with('add.countryCode')
            ->with('addedVia');

        $qb->andWhere($qb->expr()->eq($this->alias . '.licence', ':licence'))
            ->setParameter('licence', $licenceId);

        if ($conditionType !== null) {
            $qb->andWhere($qb->expr()->eq($this->alias . '.conditionType', ':conditionType'))
                ->setParameter('conditionType', $conditionType);
        }

        return $qb->getQuery()->getResult();
    }

    /**
     * Fetch a list of ConditionUndertaking for an s4.
     *
     * @param int $s4Id S4 id
     *
     * @return array
     */
    public function fetchListForS4($s4Id)
    {
        /* @var \Doctrine\Orm\QueryBuilder $qb*/
        $qb = $this->createQueryBuilder();

        $qb->andWhere($qb->expr()->eq($this->alias . '.s4', ':s4Id'))
            ->setParameter('s4Id', $s4Id);

        return $qb->getQuery()->getResult();
    }

    /**
     * Fetch a list of delta ConditionUndertakings
     *
     * @param int $id ConditionUndertaking ID
     *
     * @return array
     */
    public function fetchListForLicConditionVariation($id)
    {
        /* @var \Doctrine\Orm\QueryBuilder $qb*/
        $qb = $this->createQueryBuilder();

        $qb->andWhere($qb->expr()->eq($this->alias . '.licConditionVariation', ':id'))
            ->setParameter('id', $id);

        return $qb->getQuery()->getResult();
    }

    /**
     * Fetch small vehicle undertakings
     *
     * @param int $licenceId licence id
     *
     * @return array
     */
    public function fetchSmallVehilceUndertakings($licenceId)
    {
        /* @var \Doctrine\Orm\QueryBuilder $qb*/
        $qb = $this->createQueryBuilder();

        $qb->andWhere($qb->expr()->eq($this->alias . '.licence', ':licence'))
            ->setParameter('licence', $licenceId);
        $qb->andWhere($qb->expr()->eq($this->alias . '.conditionType', ':conditionType'))
            ->setParameter('conditionType', Entity::TYPE_UNDERTAKING);
        $qb->andWhere($qb->expr()->like($this->alias . '.notes', ':note'))
            ->setParameter('note', '%' . Entity::SMALL_VEHICLE_UNDERTAKINGS . '%');

        return $qb->getQuery()->getResult();
    }

    /**
     * Whether this licence id has the light goods vehicle undertakings
     *
     * @param int $licenceId licence id
     *
     * @return bool
     */
    public function hasLightGoodsVehicleUndertakings($licenceId)
    {
        $qb = $this->createQueryBuilder();

        $qb->select('count(' . $this->alias . '.id)');
        $qb->andWhere($qb->expr()->eq($this->alias . '.licence', ':licence'))
            ->setParameter('licence', $licenceId);
        $qb->andWhere($qb->expr()->eq($this->alias . '.conditionType', ':conditionType'))
            ->setParameter('conditionType', Entity::TYPE_UNDERTAKING);
        $qb->andWhere($qb->expr()->eq($this->alias . '.notes', ':note'))
            ->setParameter('note', Entity::LIGHT_GOODS_VEHICLE_UNDERTAKINGS);

        return $qb->getQuery()->getSingleScalarResult() > 0;
    }

    /**
     * Remove from Variation, after delete CUs from licence
     *
     * @param array $ids Condition Undertaking identifiers
     *
     * @return int
     */
    public function deleteFromVariations(array $ids)
    {
        /* @var \Doctrine\Orm\QueryBuilder $qb*/
        $qb = $this->createQueryBuilder();
        $qb
            ->andWhere(
                $qb->expr()->in($this->alias . '.licConditionVariation', ':CU_IDS')
            )
            ->setParameter('CU_IDS', $ids);

        $cuInVar = $qb->getQuery()->getResult(Query::HYDRATE_OBJECT);

        foreach ($cuInVar as $cu) {
            $this->delete($cu);
        }

        return count($cuInVar);
    }
}

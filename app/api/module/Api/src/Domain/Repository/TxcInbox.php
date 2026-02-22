<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\Ebsr\TxcInbox as Entity;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Transfer\Query\QueryInterface;

/**
 * TxcInbox
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class TxcInbox extends AbstractRepository
{
    protected $entity = Entity::class;

    /**
     * Fetch a list for an organisation
     *
     * @param int $organisation organisation id
     *
     * @return array
     */
    public function fetchByOrganisation($organisation)
    {
        $doctrineQb = $this->createQueryBuilder();

        $doctrineQb->andWhere($doctrineQb->expr()->eq($this->alias . '.organisation', ':organisation'))
            ->setParameter('organisation', $organisation);

        return $doctrineQb->getQuery()->getResult();
    }

    /**
     * Fetch a list of unread docs filtered by local authority, submission type and status for a given bus reg id
     *
     * @param int $busReg         bus reg id
     * @param int $organisationId organisation id
     * @param int $hydrateMode    doctrine hydrate mode
     *
     * @return array
     */
    public function fetchListForOrganisationByBusReg($busReg, $organisationId, $hydrateMode = Query::HYDRATE_OBJECT)
    {
        /* @var \Doctrine\Orm\QueryBuilder $qb*/
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()->modifyQuery($qb)
            ->withRefdata()
            ->with('busReg', 'b');

        $qb->where($qb->expr()->eq('b.id', ':busReg'))
            ->setParameter('busReg', $busReg);

        $qb->andWhere($qb->expr()->isNull($this->alias . '.localAuthority'));
        $qb->andWhere($qb->expr()->eq($this->alias . '.organisation', ':organisation'))
            ->setParameter('organisation', $organisationId);

        return $qb->getQuery()->getResult($hydrateMode);
    }

    /**
     * Fetch a list of unread docs filtered by local authority, submission type and status for a given bus reg id
     *
     * @param int $busReg           bus reg id
     * @param int $localAuthorityId local authority id
     * @param int $hydrateMode      doctrine hydrate mode
     *
     * @return array
     */
    public function fetchListForLocalAuthorityByBusReg($busReg, $localAuthorityId, $hydrateMode = Query::HYDRATE_OBJECT)
    {
        /* @var \Doctrine\Orm\QueryBuilder $qb*/
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()->modifyQuery($qb)
            ->withRefdata()
            ->with('busReg', 'b');

        $qb->where($qb->expr()->eq('b.id', ':busReg'))
            ->setParameter('busReg', $busReg);

        if (empty($localAuthorityId)) {
            $qb->andWhere($qb->expr()->isNull($this->alias . '.localAuthority'));
        } else {
            $qb->andWhere($qb->expr()->eq($this->alias . '.fileRead', '0'));
            $qb->andWhere($qb->expr()->eq($this->alias . '.localAuthority', ':localAuthority'))
                ->setParameter('localAuthority', $localAuthorityId);
        }

        return $qb->getQuery()->getResult($hydrateMode);
    }

    /**
     * Fetch a group of records by id list and filter by Local authority ID to prevent the need for validation
     *
     * @param array $ids              list of ids to return
     * @param int   $localAuthorityId local authority filter
     * @param int   $hydrateMode      hydrate mode
     *
     * @return array TxcInbox records
     */
    public function fetchByIdsForLocalAuthority(array $ids, $localAuthorityId, $hydrateMode = Query::HYDRATE_OBJECT)
    {
        if (empty($ids)) {
            return [];
        }

        /* @var \Doctrine\Orm\QueryBuilder $qb*/
        $qb = $this->createQueryBuilder();

        $this->getQueryBuilder()->modifyQuery($qb);

        $qb->andWhere($qb->expr()->eq('m.localAuthority', ':localAuthority'))
            ->setParameter('localAuthority', $localAuthorityId)
        ->andWhere($qb->expr()->in('m.id', $ids));

        return $qb->getQuery()->getResult($hydrateMode);
    }

    /**
     * Applies list filters
     *
     * @param QueryBuilder   $qb              doctrine query builder
     * @param QueryInterface $query           the query
     * @param array          $compositeFields composite fields
     *
     * @return void
     */
    #[\Override]
    protected function buildDefaultListQuery(QueryBuilder $qb, QueryInterface $query, $compositeFields = [])
    {
        parent::buildDefaultListQuery($qb, $query, $compositeFields);

        // join in person details
        $this->getQueryBuilder()->with($this->alias . '.busReg', 'b')
            ->with('b.ebsrSubmissions', 'e')
            ->with('b.licence', 'l')
            ->with('b.otherServices')
            ->with('l.organisation');
    }

    /**
     * Applies list filters
     *
     * @param QueryBuilder   $qb    doctrine query builder
     * @param QueryInterface $query the query
     *
     * @return void
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        if (method_exists($query, 'getLocalAuthority') && !empty($query->getLocalAuthority())) {
            $qb->andWhere($qb->expr()->eq($this->alias . '.localAuthority', ':localAuthority'))
                ->setParameter('localAuthority', $query->getLocalAuthority());
        }
        if (method_exists($query, 'getStatus') && !empty($query->getStatus())) {
            $qb->andWhere($qb->expr()->eq('b.status', ':status'))
                ->setParameter('status', $query->getStatus());
        }
        if (method_exists($query, 'getSubType') && !empty($query->getSubType())) {
            $qb->andWhere($qb->expr()->eq('e.ebsrSubmissionType', ':ebsrSubmissionType'))
                ->setParameter('ebsrSubmissionType', $query->getSubType());
        }
        $qb->andWhere($qb->expr()->eq($this->alias . '.fileRead', '0'));
    }

    /**
     * Get a list of TxcInbox entities that are linked to a document
     *
     * @param mixed $documentId Document ID
     *
     * @return array
     */
    public function fetchLinkedToDocument(mixed $documentId)
    {
        /* @var \Doctrine\Orm\QueryBuilder $qb*/
        $qb = $this->createQueryBuilder();

        $qb->orWhere($qb->expr()->eq($this->alias . '.zipDocument', ':documentId'))
            ->orWhere($qb->expr()->eq($this->alias . '.routeDocument', ':documentId'))
            ->orWhere($qb->expr()->eq($this->alias . '.pdfDocument', ':documentId'))
            ->setParameter('documentId', $documentId);

        return $qb->getQuery()->getResult();
    }
}

<?php

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Entity\View\DocumentSearchView as Entity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Dvsa\Olcs\Transfer\Query as TransferQry;
use Dvsa\Olcs\Utils\Constants\FilterOptions;

/**
 * Document Search View
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DocumentSearchView extends AbstractReadonlyRepository
{
    /**
     * Setting to false removes the unnecessary DISTINCT clause from pagination queries
     * @see http://doctrine-orm.readthedocs.io/projects/doctrine-orm/en/latest/tutorials/pagination.html
     *
     * @var bool
     */
    protected $fetchJoinCollection = false;

    protected $entity = Entity::class;

    /**
     * Get a distinct list of file extensions
     *
     * @param QueryInterface $query Query from DocumentList
     *
     * @return array
     */
    public function fetchDistinctListExtensions(QueryInterface $query)
    {
        $qb = $this->createQueryBuilder();
        $qb->select('DISTINCT m.extension');

        $this->buildDefaultListQuery($qb, $query);
        $this->applyListJoins($qb);
        $this->applyListFilters($qb, $query);

        $result = $qb->getQuery()->getResult(\Doctrine\ORM\Query::HYDRATE_ARRAY);

        // flatten the array and remove blanks
        $list = [];
        foreach ($result as $row) {
            if (!empty($row['extension'])) {
                $list[] = $row['extension'];
            }
        }

        return $list;
    }

    /**
     * Apply filters
     *
     * @param QueryBuilder                      $qb    Query Builder
     * @param TransferQry\Document\DocumentList $query Api Query
     *
     * @return void
     */
    protected function applyListFilters(QueryBuilder $qb, QueryInterface $query)
    {
        //sometimes we want only the records which aren't linked to a particular licence, case, bus reg etc.
        if ($query->getOnlyUnlinked() === 'Y') {
            $qb->andWhere(
                $qb->expr()->eq('m.identifier', ':identifier')
            );
            $qb->setParameter('identifier', Entity::IDENTIFIER_UNLINKED);
        }

        if ($query->getIsExternal() !== null) {
            $qb->andWhere(
                $qb->expr()->eq('m.isExternal', ':isExternal')
            );
            $qb->setParameter('isExternal', $query->getIsExternal() == 'Y' ? 1 : 0);
        }

        if ($query->getCategory() !== null) {
            $qb->andWhere(
                $qb->expr()->eq('m.category', $query->getCategory())
            );
        }

        if (!empty($query->getDocumentSubCategory())) {
            $qb->andWhere(
                $qb->expr()->in('m.documentSubCategory', $query->getDocumentSubCategory())
            );
        }

        if (!empty($query->getFormat())) {
            $qb->andWhere(
                $qb->expr()->eq('m.extension', ':extension')
            );
            $qb->setParameter('extension', $query->getFormat());
        }

        //  check if should show only items related to current object
        $isShowSelfOnly = ($query->getShowDocs() === FilterOptions::SHOW_SELF_ONLY);
        if ($isShowSelfOnly) {
            $appId = $query->getApplication();
            if ($appId !== null) {
                $qb->andWhere(
                    $qb->expr()->eq($this->alias . '.applicationId', ':APP_ID')
                );
                $qb->setParameter('APP_ID', $appId);
            }

            $caseId = $query->getCase();
            if ($caseId !== null) {
                $qb->andWhere(
                    $qb->expr()->eq($this->alias . '.caseId', ':CASE_ID')
                );
                $qb->setParameter('CASE_ID', $caseId);
            }

            $busRegId = $query->getBusReg();
            if ($busRegId !== null) {
                $qb->andWhere(
                    $qb->expr()->eq($this->alias . '.busRegId', ':BUS_REG_ID')
                );
                $qb->setParameter('BUS_REG_ID', $busRegId);
            }

            $irhpApplicationId = $query->getIrhpApplication();
            if ($irhpApplicationId !== null) {
                $qb->andWhere(
                    $qb->expr()->eq($this->alias . '.irhpApplicationId', ':IRHP_APPLICATION_ID')
                );
                $qb->setParameter('IRHP_APPLICATION_ID', $irhpApplicationId);
            }
        } elseif ($query->getShowDocs() === FilterOptions::EXCLUDE_IRHP) {
            $qb->andWhere(
                $qb->expr()->isNull($this->alias . '.irhpApplicationId')
            );
        }

        $idExpressions = [];

        if ($query->getLicence() !== null) {
            $idExpressions[] = $qb->expr()->eq(
                'm.licenceId',
                ':licence'
            );
            $qb->setParameter('licence', $query->getLicence());
        }

        if ($query->getTransportManager() !== null) {
            $idExpressions[] = $qb->expr()->eq(
                'm.tmId',
                ':tm'
            );
            $qb->setParameter('tm', $query->getTransportManager());
        }

        if ($query->getCase() !== null && !$isShowSelfOnly) {
            $idExpressions[] = $qb->expr()->eq(
                'm.caseId',
                ':case'
            );
            $qb->setParameter('case', $query->getCase());
        }

        if ($query->getIrfoOrganisation() !== null) {
            $idExpressions[] = $qb->expr()->eq(
                'm.irfoOrganisationId',
                ':irfoOrganisation'
            );
            $qb->setParameter('irfoOrganisation', $query->getIrfoOrganisation());
        }

        if (!empty($idExpressions)) {
            $expr = $qb->expr();

            $qb->andWhere(
                call_user_func_array($expr->orX(...), $idExpressions)
            );
        }
    }
}

<?php

/**
 * Licence Operating Centre Repository
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Doctrine\ORM\Query\Expr\Join;
use Dvsa\Olcs\Api\Entity\Cases\Complaint as ComplaintEntity;
use Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre as Entity;

/**
 * Licence Operating Centre Repository
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class LicenceOperatingCentre extends AbstractRepository
{
    protected $entity = Entity::class;
    protected $alias = 'loc';

    /**
     * Fetch a list of Licence Operating Centres for a Licence
     *
     * @param int $licenceId
     *
     * @return array
     */
    public function fetchByLicence($licenceId)
    {
        $dqb = $this->createQueryBuilder();
        $this->getQueryBuilder()->modifyQuery($dqb)
            ->withRefdata()
            ->with('operatingCentre', 'oc')
            ->with('oc.address');

        $dqb->andWhere($dqb->expr()->eq('loc.licence', ':licenceId'))
            ->setParameter('licenceId', $licenceId);

        return $dqb->getQuery()->getResult();
    }

    public function fetchByLicenceIdForOperatingCentres($licenceId, $query = null)
    {
        $qb = $this->createQueryBuilder();

        $qb->leftJoin('loc.s4', 's4');
        $qb->innerJoin('loc.operatingCentre', 'oc');
        $qb->innerJoin('oc.address', 'oca');
        $qb->leftJoin('oca.countryCode', 'ocac');
        $qb->leftJoin('oc.complaints', 'occ', Join::WITH, $qb->expr()->eq('occ.status', ':complaintStatus'));
        $qb->setParameter('complaintStatus', ComplaintEntity::COMPLAIN_STATUS_OPEN);

        $qb->andWhere(
            $qb->expr()->eq('loc.licence', ':licence')
        );
        $qb->setParameter('licence', $licenceId);

        $qb->addSelect('s4');
        $qb->addSelect('oc');
        $qb->addSelect('oca');
        $qb->addSelect('ocac');
        $qb->addSelect('occ');

        if ($query !== null && method_exists($query, 'getSort') && $query->getSort()) {
            $qb->addSelect(
                "concat(ifnull(oca.addressLine1,''),ifnull(oca.addressLine2,''),ifnull(oca.addressLine3,''),"
                . "ifnull(oca.addressLine4,''),ifnull(oca.town,'')) as adr"
            );
            $this->buildDefaultListQuery($qb, $query, ['adr']);
        } else {
            $qb->orderBy('oca.id', 'ASC');
        }
        return $this->maybeRemoveAdrColumn($qb->getQuery()->getArrayResult());
    }

    /**
     * Doctrine creates another one nested array level for O/C in case of composite expression usage.
     * We needed this column just for sorting so removing it after getting result.
     *
     * @param $result
     * @return array
     */
    public function maybeRemoveAdrColumn($result)
    {
        $mergedOc = [];
        foreach ($result as $oc) {
            // check key exists rather than "isset" as it could be null
            if (array_key_exists('adr', $oc)) {
                $mergedOc[] = $oc[0];
            } else {
                $mergedOc[] = $oc;
            }
        }
        $result = $mergedOc;
        return $result;
    }
}

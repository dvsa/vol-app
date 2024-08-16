<?php

/**
 * IrhpPermitApplication
 */

namespace Dvsa\Olcs\Api\Domain\Repository;

use Dvsa\Olcs\Api\Entity\IrhpInterface;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication as Entity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use RuntimeException;

class IrhpPermitApplication extends AbstractRepository
{
    protected $entity = Entity::class;

    public function getByIrhpApplicationWithStockInfo($irhpApplicationId)
    {
        return $this->getEntityManager()->createQueryBuilder()
            ->select(
                'ipa as irhpPermitApplication, ips.validTo as validTo, ips.id as stockId, IDENTITY(ips.country) as countryId'
            )
            ->from(Entity::class, 'ipa')
            ->innerJoin('ipa.irhpPermitWindow', 'ipw')
            ->innerJoin('ipw.irhpPermitStock', 'ips')
            ->where('IDENTITY(ipa.irhpApplication) = ?1')
            ->setParameter(1, $irhpApplicationId)
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns the sum of all euro5/euro6 permits required within the scope of the specified stock and applications
     * that are in the awating fee status
     *
     * @param int $stockId
     * @param string $emissionsCategoryId
     *
     * @return int
     */
    public function getRequiredPermitCountWhereApplicationAwaitingPayment($stockId, $emissionsCategoryId)
    {
        $mappings = [
            RefData::EMISSIONS_CATEGORY_EURO5_REF => 'requiredEuro5',
            RefData::EMISSIONS_CATEGORY_EURO6_REF => 'requiredEuro6'
        ];

        if (!isset($mappings[$emissionsCategoryId])) {
            throw new RuntimeException(
                sprintf(
                    'Emissions category id %s is not supported',
                    $emissionsCategoryId
                )
            );
        }

        $fieldName = $mappings[$emissionsCategoryId];

        $requiredPermitCount = $this->getEntityManager()->createQueryBuilder()
            ->select('sum(ipa.' . $fieldName . ')')
            ->from(Entity::class, 'ipa')
            ->innerJoin('ipa.irhpPermitWindow', 'ipw')
            ->innerJoin('ipa.irhpApplication', 'ia')
            ->where('IDENTITY(ipw.irhpPermitStock) = ?1')
            ->andWhere('ia.status = ?2')
            ->setParameter(1, $stockId)
            ->setParameter(2, IrhpInterface::STATUS_AWAITING_FEE)
            ->getQuery()
            ->getSingleScalarResult();

        if (is_null($requiredPermitCount)) {
            $requiredPermitCount = 0;
        }

        return $requiredPermitCount;
    }
}

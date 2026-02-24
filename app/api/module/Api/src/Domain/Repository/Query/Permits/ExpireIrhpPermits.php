<?php

namespace Dvsa\Olcs\Api\Domain\Repository\Query\Permits;

use Doctrine\DBAL\Connection;
use Dvsa\Olcs\Api\Domain\Repository\Query\AbstractRawQuery;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermit;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;

/**
 * Expire IRHP permits
 */
class ExpireIrhpPermits extends AbstractRawQuery
{
    /** @var array $templateMap */
    protected $templateMap = [
        'ip' => IrhpPermit::class,
        'ipr' => IrhpPermitRange::class,
        'ips' => IrhpPermitStock::class,
    ];

    /** @var string $queryTemplate */
    protected $queryTemplate = 'UPDATE {ip}
      INNER JOIN {ipr} ON {ipr.id} = {ip.irhpPermitRange}
      INNER JOIN {ips} ON {ips.id} = {ipr.irhpPermitStock}
      SET {ip.status} = :expiredStatus,
        {ip.expiryDate} = NOW(),
        {ip.lastModifiedOn} = NOW(),
        {ip.lastModifiedBy} = :currentUserId,
        {ip.version} = {ip.version} + 1
      WHERE {ip.status} IN (:validStatuses)
      AND (
        (
          {ips.irhpPermitType} = :ecmtRemovalTypeId
          AND {ip.expiryDate} IS NOT NULL
          AND {ip.expiryDate} < :endDate
        )
        OR (
          {ips.irhpPermitType} != :ecmtRemovalTypeId
          AND {ips.validTo} IS NOT NULL
          AND {ips.validTo} < :endDate
        )
      )';

    /**
     * {@inheritdoc}
     */
    #[\Override]
    protected function getParams()
    {
        $today = new DateTime();

        return [
            'expiredStatus' => IrhpPermit::STATUS_EXPIRED,
            'validStatuses' => IrhpPermit::$validStatuses,
            'ecmtRemovalTypeId' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL,
            'endDate' => $today->format('Y-m-d')
        ];
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    protected function getParamTypes()
    {
        return [
            'validStatuses' => Connection::PARAM_STR_ARRAY,
        ];
    }
}

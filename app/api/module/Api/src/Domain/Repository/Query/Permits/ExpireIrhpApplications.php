<?php

namespace Dvsa\Olcs\Api\Domain\Repository\Query\Permits;

use Doctrine\DBAL\Connection;
use Dvsa\Olcs\Api\Domain\Repository\Query\AbstractRawQuery;
use Dvsa\Olcs\Api\Entity\IrhpInterface;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermit;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;

/**
 * Expire IRHP applications
 */
class ExpireIrhpApplications extends AbstractRawQuery
{
    /** @var array $templateMap */
    protected $templateMap = [
        'ia' => IrhpApplication::class,
        'ipa' => IrhpPermitApplication::class,
        'ip' => IrhpPermit::class,
    ];

    /** @var string $queryTemplate */
    protected $queryTemplate = 'UPDATE {ia}
      SET {ia.status} = :expiredStatus,
        {ia.expiryDate} = NOW(),
        {ia.lastModifiedOn} = NOW(),
        {ia.lastModifiedBy} = :currentUserId,
        {ia.version} = {ia.version} + 1
      WHERE {ia.status} = :validStatus
        AND {ia.irhpPermitType} NOT IN (:certificatePermitTypes)
        AND {ia.id} NOT IN (
          SELECT {ipa.irhpApplication}
          FROM {ipa}
          INNER JOIN {ip} ON {ip.irhpPermitApplication} = {ipa.id} AND {ip.status} IN (:permitValidStatuses)
          WHERE {ipa.irhpApplication} IS NOT NULL
        )';

    /**
     * {@inheritdoc}
     */
    #[\Override]
    protected function getParams()
    {
        return [
            'expiredStatus' => IrhpInterface::STATUS_EXPIRED,
            'validStatus' => IrhpInterface::STATUS_VALID,
            'permitValidStatuses' => IrhpPermit::$validStatuses,
            'certificatePermitTypes' => IrhpPermitType::CERTIFICATE_TYPES,
        ];
    }

    /**
     * {@inheritdoc}
     */
    #[\Override]
    protected function getParamTypes()
    {
        return [
            'permitValidStatuses' => Connection::PARAM_STR_ARRAY,
            'certificatePermitTypes' => Connection::PARAM_INT_ARRAY,
        ];
    }
}

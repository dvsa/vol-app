<?php

namespace Dvsa\Olcs\Api\Domain\Repository\Query\Bus;

use Dvsa\Olcs\Api\Domain\Repository\Query\AbstractRawQuery;
use Dvsa\Olcs\Api\Entity\Bus\BusReg;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;

/**
 * Expire bus registrations
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class Expire extends AbstractRawQuery
{
    /** @var array $templateMap */
    protected $templateMap = [
        'br' => BusReg::class
    ];

    /** @var string $queryTemplate */
    protected $queryTemplate = 'UPDATE {br}
      SET {br.status} = :expiredStatus,
        {br.revertStatus} = :registeredStatus,
        {br.lastModifiedOn} = NOW(),
        {br.lastModifiedBy} = :currentUserId,
        {br.version} = {br.version} + 1
      WHERE {br.status} = :registeredStatus
      AND {br.endDate} <= :endDate';

    /**
     * Get the default query params
     *
     * @return array
     */
    #[\Override]
    protected function getParams()
    {
        $today = new DateTime();

        return [
            'expiredStatus' => BusReg::STATUS_EXPIRED,
            'registeredStatus' => BusReg::STATUS_REGISTERED,
            'endDate' => $today->format('Y-m-d')
        ];
    }
}

<?php

/**
 * Expire All For Licence
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository\Query\CommunityLicence;

use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\CommunityLic\CommunityLic;
use Dvsa\Olcs\Api\Domain\Repository\Query\AbstractRawQuery;

/**
 * Expire All For Licence
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ExpireAllForLicence extends AbstractRawQuery
{
    protected $templateMap = [
        'cl' => CommunityLic::class
    ];

    protected $queryTemplate = 'UPDATE {cl}
      SET {cl.status} = :status, {cl.expiredDate} = :expiredDate,
        {cl.lastModifiedOn} = NOW(), {cl.lastModifiedBy} = :currentUserId
      WHERE {cl.expiredDate} IS NULL AND {cl.licence} = :licence';

    /**
     * {@inheritdoc}
     */
    #[\Override]
    protected function getParams()
    {
        $today = new DateTime();

        return [
            'status' => CommunityLic::STATUS_EXPIRED,
            'expiredDate' => $today->format('Y-m-d H:i:s')
        ];
    }
}

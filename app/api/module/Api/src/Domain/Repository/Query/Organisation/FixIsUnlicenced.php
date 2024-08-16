<?php

namespace Dvsa\Olcs\Api\Domain\Repository\Query\Organisation;

use Dvsa\Olcs\Api\Domain\Repository\Query\AbstractRawQuery;
use Dvsa\Olcs\Api\Entity;

/**
 * Update isUnlicenced flag where operators no longer have unlicenced licences
 */
class FixIsUnlicenced extends AbstractRawQuery
{
    protected $templateMap = [
        'o' => Entity\Organisation\Organisation::class,
        'l' => Entity\Licence\Licence::class,
    ];

    protected $queryTemplate = 'UPDATE {o} 
        SET {o.isUnlicensed} = 0,
            {o.lastModifiedOn} = NOW(),
            {o.lastModifiedBy} = :currentUserId
        WHERE {o.isUnlicensed} = 1
            AND {o.id} NOT IN (
                SELECT {l.organisation} FROM {l}
                WHERE {l.status} = \'lsts_unlicenced\'
            )';
}

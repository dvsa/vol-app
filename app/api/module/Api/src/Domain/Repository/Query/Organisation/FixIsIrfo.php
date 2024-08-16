<?php

namespace Dvsa\Olcs\Api\Domain\Repository\Query\Organisation;

use Dvsa\Olcs\Api\Domain\Repository\Query\AbstractRawQuery;
use Dvsa\Olcs\Api\Entity;

/**
 * Update isIrfo flag where operators are no longer irfo
 */
class FixIsIrfo extends AbstractRawQuery
{
    protected $templateMap = [
        'o' => Entity\Organisation\Organisation::class,
        'ipa' => Entity\Irfo\IrfoPsvAuth::class,
        'igp' => Entity\Irfo\IrfoGvPermit::class,
    ];

    protected $queryTemplate = 'UPDATE {o}
        LEFT JOIN {ipa} ON {ipa.organisation} = {o.id}
        LEFT JOIN {igp} ON {igp.organisation} = {o.id}
        SET {o.isIrfo} = 0,
            {o.lastModifiedOn} = NOW(),
            {o.lastModifiedBy} = :currentUserId
        WHERE {o.type} <> \'org_t_ir\'
            AND {ipa.id} IS NULL
            AND {igp.id} IS NULL
            AND {o.isIrfo} <> 0;';
}

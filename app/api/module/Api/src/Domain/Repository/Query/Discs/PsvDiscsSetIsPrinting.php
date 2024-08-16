<?php

/**
 * PSV Discs Set isPrinting
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository\Query\Discs;

use Dvsa\Olcs\Api\Domain\Repository\Query\AbstractRawQuery;
use Dvsa\Olcs\Api\Entity\Licence\PsvDisc;

/**
 * PSV Discs Set isPrinting
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class PsvDiscsSetIsPrinting extends AbstractRawQuery
{
    protected $templateMap = [
        'pd' => PsvDisc::class
    ];

    protected $queryTemplate = 'UPDATE {pd}
      SET {pd.isPrinting} = :isPrinting,
        {pd.lastModifiedOn} = NOW(), {pd.lastModifiedBy} = :currentUserId
      WHERE {pd.id} IN (:ids)';
}

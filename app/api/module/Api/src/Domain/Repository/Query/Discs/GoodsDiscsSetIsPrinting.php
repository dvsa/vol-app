<?php

/**
 * Goods Discs Set isPrinting
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\Repository\Query\Discs;

use Dvsa\Olcs\Api\Domain\Repository\Query\AbstractRawQuery;
use Dvsa\Olcs\Api\Entity\Vehicle\GoodsDisc;

/**
 * Goods Discs Set isPrinting
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class GoodsDiscsSetIsPrinting extends AbstractRawQuery
{
    protected $templateMap = [
        'gd' => GoodsDisc::class
    ];

    protected $queryTemplate = 'UPDATE {gd}
      SET {gd.isPrinting} = :isPrinting,
        {gd.lastModifiedOn} = NOW(), {gd.lastModifiedBy} = :currentUserId
      WHERE {gd.id} IN (:ids)';
}

<?php

namespace Dvsa\Olcs\Api\Domain\Repository\Query\ViStoredProcedures;

use Dvsa\Olcs\Api\Domain\Repository\Query\AbstractRawQuery;

/**
 * Clear VI indicators for trading names
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ViTnmComplete extends AbstractRawQuery
{
    protected $queryTemplate = 'CALL vi_tnm_complete (:tradingNameId)';
}

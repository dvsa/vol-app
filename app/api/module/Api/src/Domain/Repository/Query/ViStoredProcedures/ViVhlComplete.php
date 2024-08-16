<?php

namespace Dvsa\Olcs\Api\Domain\Repository\Query\ViStoredProcedures;

use Dvsa\Olcs\Api\Domain\Repository\Query\AbstractRawQuery;

/**
 * Clear VI indicators for licence vehicles
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ViVhlComplete extends AbstractRawQuery
{
    protected $queryTemplate = 'CALL vi_vhl_complete (:vehicleId, :licenceId)';
}

<?php

namespace Dvsa\Olcs\Api\Domain\Repository\Query\ViStoredProcedures;

use Dvsa\Olcs\Api\Domain\Repository\Query\AbstractRawQuery;

/**
 * Clear VI indicators for operating centres
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ViOcComplete extends AbstractRawQuery
{
    protected $queryTemplate = 'CALL vi_oc_complete (:operatingCentreId)';
}

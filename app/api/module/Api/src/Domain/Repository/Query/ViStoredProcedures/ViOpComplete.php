<?php

namespace Dvsa\Olcs\Api\Domain\Repository\Query\ViStoredProcedures;

use Dvsa\Olcs\Api\Domain\Repository\Query\AbstractRawQuery;

/**
 * Clear VI indicators for operators
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class ViOpComplete extends AbstractRawQuery
{
    protected $queryTemplate = 'CALL vi_op_complete (:licenceId)';
}

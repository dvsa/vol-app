<?php

/**
 * Stock alignment report
 */

namespace Dvsa\Olcs\Transfer\Query\Permits;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * @Transfer\RouteName("backend/permits/stock-alignment-report")
 */
class StockAlignmentReport extends AbstractQuery
{
    use Identity;
}

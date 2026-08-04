<?php

/**
 * Max permits by stock
 */

namespace Dvsa\Olcs\Transfer\Query\IrhpApplication;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * @Transfer\RouteName("backend/irhp-application/max-stock-permits-by-application")
 */
class MaxStockPermitsByApplication extends AbstractQuery
{
    use Identity;
}

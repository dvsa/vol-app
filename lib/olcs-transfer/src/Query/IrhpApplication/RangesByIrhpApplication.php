<?php

/**
 * Get stock ranges by irhp application
 */

namespace Dvsa\Olcs\Transfer\Query\IrhpApplication;

use Dvsa\Olcs\Transfer\FieldType\Traits\IrhpApplication;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/irhp-application/ranges-by-application")
 */
class RangesByIrhpApplication extends AbstractQuery
{
    use IrhpApplication;
}

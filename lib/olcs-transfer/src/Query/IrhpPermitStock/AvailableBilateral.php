<?php

/**
 * Get list of available bilateral stocks by country
 */

namespace Dvsa\Olcs\Transfer\Query\IrhpPermitStock;

use Dvsa\Olcs\Transfer\FieldType\Traits\Country;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/irhp-permit-stock/available-bilateral")
 */
class AvailableBilateral extends AbstractQuery
{
    use Country;
}

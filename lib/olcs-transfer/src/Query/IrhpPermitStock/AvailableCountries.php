<?php

/**
 * Get list of all available countries
 */

namespace Dvsa\Olcs\Transfer\Query\IrhpPermitStock;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/irhp-permit-stock/available-countries")
 */
class AvailableCountries extends AbstractQuery
{
}

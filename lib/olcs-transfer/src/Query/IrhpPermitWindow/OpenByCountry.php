<?php

/**
 * Get open Permit Windows by country
 *
 * @author Andy Newton <andy@vitri.ltd>
 */

namespace Dvsa\Olcs\Transfer\Query\IrhpPermitWindow;

use Dvsa\Olcs\Transfer\FieldType\Traits\Countries;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/irhp-permit-window/open-by-country")
 */
class OpenByCountry extends AbstractQuery
{
    use Countries;
}

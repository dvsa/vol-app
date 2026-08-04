<?php

/**
 * Get open Permit Windows by type
 *
 * @author Andy Newton <andy@vitri.ltd>
 */

namespace Dvsa\Olcs\Transfer\Query\IrhpPermitWindow;

use Dvsa\Olcs\Transfer\FieldType\Traits\IrhpPermitType;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/irhp-permit-window/open-by-type")
 */
class OpenByType extends AbstractQuery
{
    use IrhpPermitType;
}

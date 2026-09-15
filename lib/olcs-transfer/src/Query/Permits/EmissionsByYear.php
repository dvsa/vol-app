<?php

/**
 * Details emissions standards applicable to years for given type
 */

namespace Dvsa\Olcs\Transfer\Query\Permits;

use Dvsa\Olcs\Transfer\FieldType\Traits\IrhpPermitType;
use Dvsa\Olcs\Transfer\FieldType\Traits\Year;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/permits/emissions-by-year")
 */
class EmissionsByYear extends AbstractQuery
{
    use Year;
    use IrhpPermitType;
}

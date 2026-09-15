<?php

/**
 * Get a list of permit stocks ready to print
 */

namespace Dvsa\Olcs\Transfer\Query\Permits;

use Dvsa\Olcs\Transfer\FieldType\Traits as FieldTypeTraits;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/permits/ready-to-print-stock")
 */
final class ReadyToPrintStock extends AbstractQuery
{
    use FieldTypeTraits\IrhpPermitType;
    use FieldTypeTraits\CountryOptional;
}

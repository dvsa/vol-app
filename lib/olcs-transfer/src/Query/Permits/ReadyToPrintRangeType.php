<?php

/**
 * Get a list of permit range types ready to print
 */

namespace Dvsa\Olcs\Transfer\Query\Permits;

use Dvsa\Olcs\Transfer\FieldType\Traits as FieldTypeTraits;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/permits/ready-to-print-range-type")
 */
final class ReadyToPrintRangeType extends AbstractQuery
{
    use FieldTypeTraits\IrhpPermitStock;
}

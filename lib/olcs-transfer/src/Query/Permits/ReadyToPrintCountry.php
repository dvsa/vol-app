<?php

/**
 * Get a list of permit countries ready to print
 */

namespace Dvsa\Olcs\Transfer\Query\Permits;

use Dvsa\Olcs\Transfer\FieldType\Traits as FieldTypeTraits;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/permits/ready-to-print-country")
 */
final class ReadyToPrintCountry extends AbstractQuery
{
    use FieldTypeTraits\IrhpPermitType;
}

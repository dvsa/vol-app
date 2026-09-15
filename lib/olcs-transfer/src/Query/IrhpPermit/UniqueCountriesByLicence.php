<?php

/**
 * Unique countries by licence
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Query\IrhpPermit;

use Dvsa\Olcs\Transfer\FieldType\Traits;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * @Transfer\RouteName("backend/irhp-permits/unique-countries-by-licence")
 */
final class UniqueCountriesByLicence extends AbstractQuery
{
    use Traits\Licence;
    use Traits\IrhpPermitType;
    use Traits\ValidOnlyOptional;
}

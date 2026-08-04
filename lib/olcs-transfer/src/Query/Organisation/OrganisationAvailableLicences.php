<?php

/**
 * Organisation Permits Query
 */

namespace Dvsa\Olcs\Transfer\Query\Organisation;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\FieldType\Traits\IrhpPermitStockOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\IrhpPermitType;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\CacheableShortTermQueryInterface;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/organisation-permits/single")
 */
class OrganisationAvailableLicences extends AbstractQuery implements CacheableShortTermQueryInterface
{
    use Identity;
    use IrhpPermitType;
    use IrhpPermitStockOptional;
}

<?php

/**
 * Establish whether the max permitted number of permits has been reached for a permit type and organisation
 */

namespace Dvsa\Olcs\Transfer\Query\Permits;

use Dvsa\Olcs\Transfer\FieldType\Traits\IrhpPermitType;
use Dvsa\Olcs\Transfer\FieldType\Traits\Organisation;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/permits/max-permitted-reached-by-type-and-organisation")
 */
class MaxPermittedReachedByTypeAndOrganisation extends AbstractQuery
{
    use IrhpPermitType;
    use Organisation;
}

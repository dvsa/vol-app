<?php

/**
 * Internal applications summary
 */

namespace Dvsa\Olcs\Transfer\Query\IrhpApplication;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\FieldType\Traits\IrhpApplicationStatusOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\Licence;

/**
 * @Transfer\RouteName("backend/irhp-application/internal-applications-summary")
 */
class InternalApplicationsSummary extends AbstractQuery
{
    use Licence;
    use IrhpApplicationStatusOptional;
}

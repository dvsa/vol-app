<?php

/**
 * Selfserve applications summary
 */

namespace Dvsa\Olcs\Transfer\Query\IrhpApplication;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\FieldType\Traits\Organisation;

/**
 * @Transfer\RouteName("backend/irhp-application/selfserve-applications-summary")
 */
class SelfserveApplicationsSummary extends AbstractQuery
{
    use Organisation;
}

<?php

/**
 * Get a list of permits ready to print for confirmation
 */

namespace Dvsa\Olcs\Transfer\Query\Permits;

use Dvsa\Olcs\Transfer\FieldType\Traits\Ids;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/permits/ready-to-print-confirm")
 */
final class ReadyToPrintConfirm extends AbstractQuery
{
    use Ids;
}

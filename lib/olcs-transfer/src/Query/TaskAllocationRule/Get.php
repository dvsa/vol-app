<?php

namespace Dvsa\Olcs\Transfer\Query\TaskAllocationRule;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * @Transfer\RouteName("backend/task-allocation-rule/single")
 */
class Get extends AbstractQuery
{
    use Identity;
}

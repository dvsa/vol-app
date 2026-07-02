<?php

namespace Dvsa\Olcs\Transfer\Query\TaskAlphaSplit;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * @Transfer\RouteName("backend/task-alpha-split/single")
 */
class Get extends AbstractQuery
{
    use Identity;
}

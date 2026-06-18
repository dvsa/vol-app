<?php

namespace Dvsa\Olcs\Transfer\Query\Cases\Si\Applied;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Class Penalty
 * @Transfer\RouteName("backend/si-penalty-applied/single")
 */
class Penalty extends AbstractQuery
{
    use Identity;
}

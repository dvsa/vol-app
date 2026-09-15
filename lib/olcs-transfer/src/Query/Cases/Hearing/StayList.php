<?php

namespace Dvsa\Olcs\Transfer\Query\Cases\Hearing;

use Dvsa\Olcs\Transfer\FieldType\Traits;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Class StayList
 * @Transfer\RouteName("backend/stay")
 */
class StayList extends AbstractQuery
{
    use Traits\Cases;
}

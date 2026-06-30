<?php

namespace Dvsa\Olcs\Transfer\Query\Cases\NonPi;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\FieldType;

/**
 * Class non-pi
 *
 * @Transfer\RouteName("backend/non-pi/named-single")
 */
class Single extends AbstractQuery
{
    use FieldType\Traits\Cases;
}

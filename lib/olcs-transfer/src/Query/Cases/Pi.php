<?php

namespace Dvsa\Olcs\Transfer\Query\Cases;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Class Pi
 * @Transfer\RouteName("backend/pi/single")
 */
class Pi extends AbstractQuery
{
    use Identity;
}

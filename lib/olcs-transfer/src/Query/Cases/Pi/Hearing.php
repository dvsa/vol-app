<?php

namespace Dvsa\Olcs\Transfer\Query\Cases\Pi;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Class Hearing
 * @Transfer\RouteName("backend/pi/hearing/single")
 */
class Hearing extends AbstractQuery
{
    use Identity;
}

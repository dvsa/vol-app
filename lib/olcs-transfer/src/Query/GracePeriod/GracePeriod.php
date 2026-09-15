<?php

namespace Dvsa\Olcs\Transfer\Query\GracePeriod;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Class GracePeriods
 * @Transfer\RouteName("backend/grace-periods/single")
 */
class GracePeriod extends AbstractQuery
{
    use Identity;
}

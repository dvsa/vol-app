<?php

namespace Dvsa\Olcs\Transfer\Query\Fee;

use Dvsa\Olcs\Transfer\FieldType\Traits\EndDate;
use Dvsa\Olcs\Transfer\FieldType\Traits\StartDate;
use Dvsa\Olcs\Transfer\FieldType\Traits\TrafficAreasOptional;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/fee/interim-refunds")
 */
class InterimRefunds extends AbstractQuery
{
    use OrderedTrait;
    use StartDate;
    use EndDate;
    use TrafficAreasOptional;
}

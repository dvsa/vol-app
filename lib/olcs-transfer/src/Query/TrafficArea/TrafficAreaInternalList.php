<?php

namespace Dvsa\Olcs\Transfer\Query\TrafficArea;

use Dvsa\Olcs\Transfer\FieldType\Traits\TrafficAreas;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;

/**
 * @Transfer\RouteName("backend/traffic-area-internal")
 */
final class TrafficAreaInternalList extends AbstractQuery implements OrderedQueryInterface
{
    use OrderedTrait;
    use TrafficAreas;
}

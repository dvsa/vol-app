<?php

/**
 * Requests to Dvsa\Olcs\Transfer\Query\Team\TeamListData are passed to
 * this query when the user has limited traffic area permissions
 */

namespace Dvsa\Olcs\Api\Domain\Query\Team;

use Dvsa\Olcs\Transfer\FieldType\Traits\TrafficAreas;
use Dvsa\Olcs\Transfer\Query\AbstractListData;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

final class TeamListByTrafficArea extends AbstractListData
{
    use TrafficAreas;
}

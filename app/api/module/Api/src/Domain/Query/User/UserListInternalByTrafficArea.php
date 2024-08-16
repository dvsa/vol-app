<?php

/**
 * The standard Dvsa\Olcs\Transfer\Query\User\UserListInternal query
 * will sometimes hand off to this one in the backend when the following is met
 *
 * 1. No team was specified in the team parameter
 * 2. The user has limited data access (GB/NI - not to be confused with read only access)
 */

namespace Dvsa\Olcs\Api\Domain\Query\User;

use Dvsa\Olcs\Transfer\FieldType\Traits\ExcludeLimitedReadOnlyOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\IsInternalTrue;
use Dvsa\Olcs\Transfer\FieldType\Traits\TeamOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\TrafficAreas;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

final class UserListInternalByTrafficArea extends AbstractQuery implements OrderedQueryInterface
{
    use OrderedTrait;
    use TeamOptional;
    use ExcludeLimitedReadOnlyOptional;
    use IsInternalTrue;
    use TrafficAreas;
}

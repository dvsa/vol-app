<?php

/**
 * This will proxy to Dvsa\Olcs\Api\Domain\Query\User\UserListInternalByTrafficArea query
 * in the backend when the following is met
 *
 * 1. No team was specified in the team parameter
 * 2. The user has limited data access (GB/NI - not to be confused with read only access)
 */

namespace Dvsa\Olcs\Transfer\Query\User;

use Dvsa\Olcs\Transfer\FieldType\Traits\ExcludeLimitedReadOnlyOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\IsInternalTrue;
use Dvsa\Olcs\Transfer\FieldType\Traits\TeamOptional;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * @Transfer\RouteName("backend/user/internal-only")
 */
final class UserListInternal extends AbstractQuery implements OrderedQueryInterface
{
    use OrderedTrait;
    use TeamOptional;
    use ExcludeLimitedReadOnlyOptional;
    use IsInternalTrue;
}

<?php

/**
 * The standard Dvsa\Olcs\Transfer\Query\User\UserList query
 * will sometimes hand off to this one in the backend when the following is met
 *
 * 1. No team was specified in the team parameter
 * 2. The user has limited data access (GB/NI - not to be confused with read only access)
 */

namespace Dvsa\Olcs\Api\Domain\Query\User;

use Dvsa\Olcs\Transfer\FieldType\Traits\IsInternalOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\OrganisationOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\RolesOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\TeamOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\TrafficAreas;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\OrderedTraitOptional;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

final class UserListByTrafficArea extends AbstractQuery implements OrderedQueryInterface
{
    use OrderedTraitOptional;
    use RolesOptional;
    use OrganisationOptional;
    use TeamOptional;
    use IsInternalOptional;
    use TrafficAreas;
}

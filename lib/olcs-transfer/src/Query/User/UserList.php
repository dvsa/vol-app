<?php

namespace Dvsa\Olcs\Transfer\Query\User;

use Dvsa\Olcs\Transfer\FieldType\Traits\IsInternalOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\OrganisationOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\RolesOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\TeamOptional;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\OrderedTraitOptional;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * @Transfer\RouteName("backend/user/internal")
 */
final class UserList extends AbstractQuery implements OrderedQueryInterface
{
    use OrderedTraitOptional;
    use RolesOptional;
    use OrganisationOptional;
    use TeamOptional;
    use IsInternalOptional;
}

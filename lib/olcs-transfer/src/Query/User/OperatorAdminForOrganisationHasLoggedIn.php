<?php

/**
 * User Selfserve
 */

namespace Dvsa\Olcs\Transfer\Query\User;

use Dvsa\Olcs\Transfer\FieldType\Traits\LastLoggedInFromOptional;
use Dvsa\Olcs\Transfer\FieldType\Traits\Organisation;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\CacheableShortTermQueryInterface;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/user/operator-admin-for-organisation-has-logged-in")
 */
class OperatorAdminForOrganisationHasLoggedIn extends AbstractQuery implements CacheableShortTermQueryInterface
{
    use Organisation;
    use LastLoggedInFromOptional;
}

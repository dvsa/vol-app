<?php

/**
 * User Selfserve
 */

namespace Dvsa\Olcs\Transfer\Query\User;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\CacheableShortTermQueryInterface;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/user/selfserve/single")
 */
class UserSelfserve extends AbstractQuery implements CacheableShortTermQueryInterface
{
    use Identity;
}

<?php

namespace Dvsa\Olcs\Transfer\Query\User;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\CacheableLongTermQueryInterface;
use Dvsa\Olcs\Transfer\Query\PublicQueryCacheInterface;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Class Role List
 * @Transfer\RouteName("backend/user/roles")
 */
class RoleList extends AbstractQuery implements CacheableLongTermQueryInterface, PublicQueryCacheInterface
{
}

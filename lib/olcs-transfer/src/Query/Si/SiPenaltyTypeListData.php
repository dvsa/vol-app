<?php

/**
 * Get a list of all Si Penalty Type for select
 */

namespace Dvsa\Olcs\Transfer\Query\Si;

use Dvsa\Olcs\Transfer\Query\AbstractListData;
use Dvsa\Olcs\Transfer\Query\CacheableLongTermQueryInterface;
use Dvsa\Olcs\Transfer\Query\PublicQueryCacheInterface;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/si/si-penalty-type/list-data")
 */
final class SiPenaltyTypeListData extends AbstractListData implements
    CacheableLongTermQueryInterface,
    PublicQueryCacheInterface
{
}

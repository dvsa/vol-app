<?php

/**
 * Sectors
 *
 * @author Tonci Vidovic <tonci.vidovic@capgemini.com>
 */

namespace Dvsa\Olcs\Transfer\Query\Permits;

use Dvsa\Olcs\Transfer\Query\CacheableLongTermQueryInterface;
use Dvsa\Olcs\Transfer\Query\PublicQueryCacheInterface;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\OrderedTraitOptional;

/**
 * @Transfer\RouteName("backend/permits/sectors")
 */
class Sectors extends AbstractQuery implements CacheableLongTermQueryInterface, PublicQueryCacheInterface
{
    use OrderedTraitOptional;
}

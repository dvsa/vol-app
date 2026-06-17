<?php

/**
 * Get a list of Countrys
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Query\ContactDetail;

use Dvsa\Olcs\Transfer\Query\PublicQueryCacheInterface;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;
use Dvsa\Olcs\Transfer\Query\CacheableLongTermQueryInterface;

/**
 * @Transfer\RouteName("backend/country")
 */
class CountryList extends AbstractQuery implements
    OrderedQueryInterface,
    CacheableLongTermQueryInterface,
    PublicQueryCacheInterface
{
    use OrderedTrait;
}

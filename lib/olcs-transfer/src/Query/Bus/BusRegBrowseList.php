<?php

namespace Dvsa\Olcs\Transfer\Query\Bus;

use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedTrait;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Class BusRegBrowseList
 * @Transfer\RouteName("backend/bus/browse")
 */
class BusRegBrowseList extends BusRegBrowseExport implements PagedQueryInterface, OrderedQueryInterface
{
    use PagedTrait;
    use OrderedTrait;
}

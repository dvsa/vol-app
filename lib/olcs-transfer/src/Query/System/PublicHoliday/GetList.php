<?php

namespace Dvsa\Olcs\Transfer\Query\System\PublicHoliday;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedTrait;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/public-holiday")
 */
class GetList extends AbstractQuery implements OrderedQueryInterface, PagedQueryInterface
{
    use OrderedTrait;
    use PagedTrait;
}

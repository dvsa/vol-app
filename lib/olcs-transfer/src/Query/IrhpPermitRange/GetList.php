<?php

/**
 * Get a list of IRHP Stocks Ranges
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */

namespace Dvsa\Olcs\Transfer\Query\IrhpPermitRange;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedTrait;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;
use Dvsa\Olcs\Transfer\FieldType\Traits\IrhpPermitStock;

/**
 * @Transfer\RouteName("backend/irhp-permit-range")
 */
final class GetList extends AbstractQuery implements PagedQueryInterface, OrderedQueryInterface
{
    use IrhpPermitStock;
    use PagedTrait;
    use OrderedTrait;
}

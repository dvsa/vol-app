<?php

/**
 * Establish whether the max permitted number of permits has been reached for a stock and licence
 */

namespace Dvsa\Olcs\Transfer\Query\Permits;

use Dvsa\Olcs\Transfer\FieldType\Traits\IrhpPermitStock;
use Dvsa\Olcs\Transfer\FieldType\Traits\Licence;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/permits/max-permitted-reached-by-stock-and-licence")
 */
class MaxPermittedReachedByStockAndLicence extends AbstractQuery
{
    use IrhpPermitStock;
    use Licence;
}

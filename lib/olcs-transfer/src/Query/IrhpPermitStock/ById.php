<?php

/**
 * Get a single Permit Stock by Id
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */

namespace Dvsa\Olcs\Transfer\Query\IrhpPermitStock;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * @Transfer\RouteName("backend/irhp-permit-stock/single")
 */
class ById extends AbstractQuery
{
    use Identity;
}

<?php

/**
 * Unlicensed Operator Vehicles
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\Olcs\Transfer\Query\Operator;

use Dvsa\Olcs\Transfer\FieldType\Traits\Organisation;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedTrait;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * @Transfer\RouteName("backend/operator-unlicensed/named-single/vehicles")
 */
class UnlicensedVehicles extends AbstractQuery implements PagedQueryInterface
{
    use Organisation;
    use PagedTrait;
}

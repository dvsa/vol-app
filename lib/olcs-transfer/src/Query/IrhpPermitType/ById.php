<?php

/**
 * Get a single Permit Type by Id
 */

namespace Dvsa\Olcs\Transfer\Query\IrhpPermitType;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * @Transfer\RouteName("backend/irhp-permit-type/single")
 */
class ById extends AbstractQuery
{
    use Identity;
}

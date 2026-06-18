<?php

/**
 * Get a single Presiding TC by Id
 *
 * @author Andy Newton <andy.newton@dvsa.gov.uk>
 */

namespace Dvsa\Olcs\Transfer\Query\Cases\PresidingTc;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;

/**
 * @Transfer\RouteName("backend/presiding-tc/single")
 */
class ById extends AbstractQuery
{
    use Identity;
}

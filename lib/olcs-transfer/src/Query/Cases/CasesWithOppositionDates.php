<?php

/**
 * Cases with Opposition dates
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Query\Cases;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * @Transfer\RouteName("backend/cases/single/opposition-dates")
 */
class CasesWithOppositionDates extends AbstractQuery
{
    use Identity;
}

<?php

/**
 * Previous Conviction
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Query\PreviousConviction;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * @Transfer\RouteName("backend/previous-conviction/single")
 */
class PreviousConviction extends AbstractQuery
{
    use Identity;
}

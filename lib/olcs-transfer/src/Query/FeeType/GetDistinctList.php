<?php

/**
 * Get a list of Fee Types
 *
 * @author Andy Newton <andy@vitri.ltd>
 */

namespace Dvsa\Olcs\Transfer\Query\FeeType;

use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * @Transfer\RouteName("backend/fee-type/fee-types-distinct")
 */
final class GetDistinctList extends AbstractQuery
{
}

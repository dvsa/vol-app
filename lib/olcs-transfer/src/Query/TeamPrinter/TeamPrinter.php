<?php

/**
 * Team Printer
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Query\TeamPrinter;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\CacheableShortTermQueryInterface;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/printer-exception/single")
 */
class TeamPrinter extends AbstractQuery implements CacheableShortTermQueryInterface
{
    use Identity;
}

<?php

/**
 * Enforcement Area
 *
 * @author Enforcement Area <alex.peshkov@valetch.co.uk>
 */

namespace Dvsa\Olcs\Transfer\Query\Licence;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * @Transfer\RouteName("backend/licence/single/enforcement-area")
 */
class EnforcementArea extends AbstractQuery
{
    use Identity;
}

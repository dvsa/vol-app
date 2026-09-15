<?php

namespace Dvsa\Olcs\Transfer\Query\System;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\OrderedTraitOptional;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Class FinancialStandingRateList
 * @Transfer\RouteName("backend/financial-standing-rate")
 */
class FinancialStandingRateList extends AbstractQuery implements OrderedQueryInterface
{
    use OrderedTraitOptional;
}

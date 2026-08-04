<?php

namespace Dvsa\Olcs\Transfer\Query\Cases;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\FieldType\Traits as FieldType;

/**
 * Class AnnualTestHistory
 * @Transfer\RouteName("backend/annual-test-history/single")
 */
class AnnualTestHistory extends AbstractQuery
{
    use FieldType\Identity;
    use FieldType\Cases;
}

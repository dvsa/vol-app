<?php

namespace Dvsa\Olcs\Transfer\Query\ContinuationDetail;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * @Transfer\RouteName("backend/continuation-detail/single/review")
 */
class Review extends AbstractQuery
{
    use Identity;
}

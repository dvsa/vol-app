<?php

namespace Dvsa\Olcs\Transfer\Query\Submission;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Class Submission
 * @Transfer\RouteName("backend/submission/single")
 */
class Submission extends AbstractQuery
{
    use Identity;
}

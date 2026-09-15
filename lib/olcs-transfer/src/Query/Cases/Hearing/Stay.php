<?php

namespace Dvsa\Olcs\Transfer\Query\Cases\Hearing;

use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Class Stay
 * @Transfer\RouteName("backend/stay/single")
 */
class Stay extends AbstractQuery
{
    use Traits\Identity;
}

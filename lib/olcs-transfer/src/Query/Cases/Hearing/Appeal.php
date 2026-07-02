<?php

namespace Dvsa\Olcs\Transfer\Query\Cases\Hearing;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\FieldType\Traits;

/**
 * Class Appeal
 * @Transfer\RouteName("backend/appeal/single")
 */
class Appeal extends AbstractQuery
{
    use Traits\Identity;
}

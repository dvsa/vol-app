<?php

namespace Dvsa\Olcs\Transfer\Query\Opposition;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Class Opposition
 * @Transfer\RouteName("backend/opposition/single")
 */
class Opposition extends AbstractQuery
{
    use Identity;
}

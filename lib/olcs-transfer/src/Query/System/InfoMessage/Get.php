<?php

namespace Dvsa\Olcs\Transfer\Query\System\InfoMessage;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;

/**
 * @Transfer\RouteName("backend/system-info-message/single")
 */
class Get extends AbstractQuery
{
    use Identity;
}

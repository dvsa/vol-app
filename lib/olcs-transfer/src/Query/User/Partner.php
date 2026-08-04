<?php

namespace Dvsa\Olcs\Transfer\Query\User;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * Class Partner
 * @Transfer\RouteName("backend/partner/single")
 */
class Partner extends AbstractQuery
{
    use Identity;
}

<?php

namespace Dvsa\Olcs\Transfer\Query\System\PublicHoliday;

use Dvsa\Olcs\Transfer\FieldType\Traits\Identity;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

#[Transfer\RouteName('backend/public-holiday/single')]
class Get extends AbstractQuery
{
    use Identity;
}

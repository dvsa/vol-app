<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Transfer\Query\DvlaSearch;

use Dvsa\Olcs\Transfer\FieldType\Traits\Vrm;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/dvla-search/vehicle")
 */
class Vehicle extends AbstractQuery
{
    use Vrm;
}

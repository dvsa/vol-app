<?php

namespace Dvsa\Olcs\Transfer\Query\LicenceVehicle;

use Dvsa\Olcs\Transfer\FieldType\Traits\Ids;
use Dvsa\Olcs\Transfer\Query\AbstractQuery;
use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

#[Transfer\RouteName('backend/licence-vehicle')]
class LicenceVehiclesById extends AbstractQuery
{
    use Ids;
}

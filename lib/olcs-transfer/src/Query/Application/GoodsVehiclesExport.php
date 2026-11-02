<?php

namespace Dvsa\Olcs\Transfer\Query\Application;

use Dvsa\Olcs\Transfer\Query\Lva\AbstractGoodsVehicles;
use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

#[Transfer\RouteName('backend/application/single/goods-vehicles/export')]
class GoodsVehiclesExport extends AbstractGoodsVehicles
{
}

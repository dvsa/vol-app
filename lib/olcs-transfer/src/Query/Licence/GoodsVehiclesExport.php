<?php

namespace Dvsa\Olcs\Transfer\Query\Licence;

use Dvsa\Olcs\Transfer\Query\Lva\AbstractGoodsVehicles;
use Dvsa\Olcs\Transfer\Util\Attribute as Transfer;

#[Transfer\RouteName('backend/licence/single/goods-vehicles/export')]
class GoodsVehiclesExport extends AbstractGoodsVehicles
{
}

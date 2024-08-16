<?php

/**
 * Goods Vehicles
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\QueryHandler\Variation;

use Dvsa\Olcs\Api\Domain\QueryHandler\Application\GoodsVehicles as ApplicationGoodsVehicles;

/**
 * Goods Vehicles
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class GoodsVehicles extends ApplicationGoodsVehicles
{
    protected $licenceVehicleMethod = 'createPaginatedVehiclesDataForVariationQuery';
}

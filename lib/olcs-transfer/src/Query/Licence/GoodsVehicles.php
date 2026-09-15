<?php

namespace Dvsa\Olcs\Transfer\Query\Licence;

use Dvsa\Olcs\Transfer\Query\Lva\AbstractGoodsVehicles;
use Dvsa\Olcs\Transfer\Query\OrderedQueryInterface;
use Dvsa\Olcs\Transfer\Query\OrderedTrait;
use Dvsa\Olcs\Transfer\Query\PagedQueryInterface;
use Dvsa\Olcs\Transfer\Query\PagedTrait;
use Dvsa\Olcs\Transfer\Util\Annotation as Transfer;

/**
 * @Transfer\RouteName("backend/licence/single/goods-vehicles")
 */
class GoodsVehicles extends AbstractGoodsVehicles implements PagedQueryInterface, OrderedQueryInterface, FiltersByVehicleIdsInterface
{
    use PagedTrait;
    use OrderedTrait;

    /**
     * @var array|null
     * @Transfer\Validator("\Dvsa\Olcs\Transfer\Validators\ValidateEach",
     *     options={
     *         "min": 1,
     *         "max": 100,
     *         "children": {
     *             {"name": "\Laminas\Validator\Digits", "options": {}},
     *             {"name": "\Laminas\Validator\GreaterThan", "options": {"min": 0}}
     *         },
     *     }
     * )
     * @Transfer\Optional
     */
    protected $vehicleIds;

    /**
     * Gets the vehicle ids which should be used to filter results.
     *
     * @return array|null
     */
    #[\Override]
    public function getVehicleIds(): ?array
    {
        return $this->vehicleIds;
    }
}

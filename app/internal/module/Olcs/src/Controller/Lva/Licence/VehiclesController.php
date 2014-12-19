<?php

/**
 * Internal Licence Vehicles Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Licence;

use Olcs\Controller\Lva\AbstractGenericVehiclesController;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;
use Common\Controller\Lva\Traits;

/**
 * Internal Licence Vehicles Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VehiclesController extends AbstractGenericVehiclesController
{
    use LicenceControllerTrait,
        Traits\LicenceGenericVehiclesControllerTrait,
        Traits\LicenceGoodsVehiclesControllerTrait,
        Traits\PsvGoodsLicenceVariationControllerTrait;

    protected $lva = 'licence';
    protected $location = 'internal';

    /**
     * This method is used to hook the trait's pre & post save methods into the parent save vehicle method
     *
     * @param array $data
     * @param string $mode
     */
    protected function saveVehicle($data, $mode)
    {
        $data = $this->preSaveVehicle($data, $mode);

        $licenceVehicleId = parent::saveVehicle($data, $mode);

        $this->postSaveVehicle($licenceVehicleId, $mode);
    }
}

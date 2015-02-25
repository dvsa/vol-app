<?php

/**
 * Internal Licence Vehicles Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Licence;

use Olcs\Controller\Interfaces\LicenceControllerInterface;
use Olcs\Controller\Lva\AbstractGenericVehiclesController;
use Olcs\Controller\Lva\Traits\LicenceControllerTrait;
use Common\Controller\Lva\Traits;

/**
 * Internal Licence Vehicles Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VehiclesController extends AbstractGenericVehiclesController implements LicenceControllerInterface
{
    use LicenceControllerTrait,
        Traits\LicenceGenericVehiclesControllerTrait,
        Traits\LicenceGoodsVehiclesControllerTrait,
        Traits\PsvGoodsLicenceVariationControllerTrait;

    protected $lva = 'licence';
    protected $location = 'internal';
}

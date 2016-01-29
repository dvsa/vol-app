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

/**
 * Internal Licence Vehicles Controller
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VehiclesController extends AbstractGenericVehiclesController implements LicenceControllerInterface
{
    use LicenceControllerTrait;

    protected $lva = 'licence';
    protected $location = 'internal';
}

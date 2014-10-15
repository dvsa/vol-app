<?php

/**
 * Vehicle Controller
 *
 * Internal - Application - Vehicle section
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Journey\Application\VehicleSafety;

use Common\Controller\Application\VehicleSafety\VehicleSafetyController;
use Common\Controller\Traits\VehicleSafety as VehicleSafetyTraits;

/**
 * Vehicle Controller
 *
 * Here we extend the Application Journey VehicleSafetyController just as we do in the External VehicleController
 *  however, we use the internal vehicle section trait instead of the external
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VehicleController extends VehicleSafetyController
{
    use VehicleSafetyTraits\VehicleSection,
        VehicleSafetyTraits\InternalGenericVehicleSection,
        VehicleSafetyTraits\ApplicationGenericVehicleSection,
        VehicleSafetyTraits\GenericApplicationVehicleSection;

    protected function parentActionSave($data, $service = null)
    {
        return parent::actionSave($data, $service);
    }
}

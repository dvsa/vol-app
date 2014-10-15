<?php

/**
 * Vehicle PSV Controller
 *
 * Internal - Application - Vehicle PSV section
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Olcs\Controller\Journey\Application\VehicleSafety;

use Common\Controller\Application\VehicleSafety\VehicleSafetyController;
use Common\Controller\Traits\VehicleSafety as VehicleSafetyTraits;

/**
 * Vehicle PSV Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VehiclePsvController extends VehicleSafetyController
{
    use VehicleSafetyTraits\VehiclePsvSection,
        VehicleSafetyTraits\InternalGenericVehicleSection,
        VehicleSafetyTraits\ApplicationGenericVehicleSection,
        VehicleSafetyTraits\GenericApplicationVehiclePsvSection;

    protected function parentActionSave($data, $service = null)
    {
        return parent::actionSave($data, $service);
    }
}

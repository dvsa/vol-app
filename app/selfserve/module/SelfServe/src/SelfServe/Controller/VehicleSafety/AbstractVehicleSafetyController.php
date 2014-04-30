<?php

/**
 * Abstract Vehicle Safety Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace SelfServe\Controller\VehicleSafety;

use SelfServe\Controller\AbstractApplicationController;

/**
 * Abstract Vehicle Safety Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
abstract class AbstractVehicleSafetyController extends AbstractApplicationController
{
    /**
     * Render the layout

     * @param object $view
     * @param string $current
     */
    protected function renderLayoutWithSubSections($view, $current = '')
    {
        $applicationId = $this->params()->fromRoute('applicationId');

        $this->setSubSections(
            array(
                'vehicles' => array(
                    'label' => 'selfserve-app-subSection-vehicle-safety-vehicle',
                    // @todo Change this to the vehicle page once complete
                    'route' => 'selfserve/vehicle-safety/vehicle-action/vehicle-add',
                    'routeParams' => array(
                        'applicationId' => $applicationId
                    )
                ),
                'safety' => array(
                    'label' => 'selfserve-app-subSection-vehicle-safety-safety',
                    'route' => 'selfserve/vehicle-safety/safety-action',
                    'routeParams' => array(
                        'applicationId' => $applicationId
                    )
                )
            )
        );

        return parent::renderLayoutWithSubSections($view, $current);
    }
}

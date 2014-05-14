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

    public function __construct()
    {
        $this->setCurrentSection('vehicle-safety');
    }

    /**
     * Render the layout

     * @param object $view
     * @param string $current
     */
    public function renderLayoutWithSubSections($view, $current = '', $journey = 'vehicle-safety', $disabled = null)
    {
        $applicationId = $this->getApplicationId();

        $this->setSubSections(
            array(
                'vehicle' => array(
                    'label' => 'selfserve-app-subSection-vehicle-safety-vehicle',
                    'route' => 'selfserve/vehicle-safety/vehicle',
                    'routeParams' => array(
                        'applicationId' => $applicationId
                    )
                ),
                'safety' => array(
                    'label' => 'selfserve-app-subSection-vehicle-safety-safety',
                    'route' => 'selfserve/vehicle-safety/safety',
                    'routeParams' => array(
                        'applicationId' => $applicationId
                    )
                )
            )
        );

        return parent::renderLayoutWithSubSections($view, $current, $journey, $disabled);
    }

    /**
     * Redirect to safety
     *
     * @return object
     */
    public function backToSafety()
    {
        $applicationId = $this->getApplicationId();

        return $this->redirectToRoute(
            'selfserve/vehicle-safety/safety',
            array('applicationId' => $applicationId)
        );
    }

    /**
     * Redirect to vehicles sections
     *
     * @return objecy
     */
    public function redirectToVehicles()
    {
        $applicationId = $this->getApplicationId();

        return $this->redirectToRoute('selfserve/vehicle-safety/vehicle', array('applicationId' => $applicationId));
    }
}
